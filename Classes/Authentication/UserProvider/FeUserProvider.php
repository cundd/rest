<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication\UserProvider;

use Cundd\Rest\Authentication\Exception\InvalidConfigurationException;
use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SensitiveParameter;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * User Provider implementation based on the `fe_users` table
 *
 * It's possible that the same username is used in different `Site`s within a
 * single TYPO3 installation. To control which users actually are allowed to
 * use the API, the storage page of allowed users has to be configured (per
 * `Site`).
 *
 * @phpstan-type UserRecord array{uid: int<1, max>, username: string, self::PASSWORD_COLUMN_NAME: string}
 */
class FeUserProvider implements UserProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Name of the password column
     */
    public const PASSWORD_COLUMN_NAME = 'tx_rest_apikey';

    private const TABLE = 'fe_users';

    public function __construct(
        private readonly PasswordHashFactory $passwordHashFactory,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function checkCredentials(
        ConfigurationProviderInterface $configurationProvider,
        string $username,
        #[SensitiveParameter]
        string $password,
    ): bool {
        if ('' === $username || '' === $password) {
            return false;
        }

        $storagePageId = $this->getStoragePageId($configurationProvider);
        if (null === $storagePageId) {
            return false;
        }

        $user = $this->getPossibleUser($username, $storagePageId);
        if (null === $user) {
            // Perform a check against a fake API-key-hash to prevent timing
            // based user enumeration
            $this->performFakePasswordCheck($password);

            return false;
        }

        return $this->checkPossibleUser($password, $user);
    }

    /**
     * @param int<1,max> $storagePageId
     *
     * @return UserRecord|null
     */
    private function getPossibleUser(
        string $username,
        int $storagePageId,
    ): ?array {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);

        $user = $queryBuilder
            ->select('uid', 'pid', 'username', self::PASSWORD_COLUMN_NAME)
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'username',
                    $queryBuilder->createNamedParameter($username)
                ),
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($storagePageId)
                ),
                $queryBuilder->expr()->neq(
                    self::PASSWORD_COLUMN_NAME,
                    $queryBuilder->createNamedParameter('')
                ),
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        if (false === $user) {
            return null;
        }

        assert(is_int($user['uid']) && $user['uid'] > 0);
        assert(is_string($user['username']));
        assert(is_string($user[self::PASSWORD_COLUMN_NAME]));

        return $user;
    }

    /**
     * @param UserRecord $user
     */
    private function checkPossibleUser(
        #[SensitiveParameter]
        string $password,
        array $user,
    ): bool {
        $passwordHashInDatabase = $user[self::PASSWORD_COLUMN_NAME];
        assert(is_string($passwordHashInDatabase));

        try {
            $hashInstance = $this->passwordHashFactory->get(
                $passwordHashInDatabase,
                'FE'
            );
        } catch (InvalidPasswordHashException $exception) {
            $this->logger?->error('Could not get Password Hash instance for API key of user {user}', [
                'user' => $user['username'],
            ]);

            return false;
        }

        if (!$hashInstance->checkPassword($password, $passwordHashInDatabase)) {
            return false;
        }

        // Given password validated but a re-hash is needed. Do so.
        if ($hashInstance->isHashUpdateNeeded($passwordHashInDatabase)) {
            $updatedHash = $hashInstance->getHashedPassword($password);
            if ($updatedHash) {
                $this->updatePasswordHashInDatabase(
                    self::TABLE,
                    (int) $user['uid'],
                    $updatedHash
                );
            }
        }

        return true;
    }

    /**
     * @return int<1,max>
     */
    private function getStoragePageId(
        ConfigurationProviderInterface $configurationProvider,
    ): ?int {
        $storagePageId = $configurationProvider
            ->getSetting('authentication.feUserProvider.storagePageId');
        if (null === $storagePageId) {
            return null;
        }

        $exceptionMessage = 'Configuration "authentication.feUserProvider.storagePageId" must be an integer > 0';
        if (!is_int($storagePageId)) {
            throw new InvalidConfigurationException(
                $exceptionMessage,
                1794745538
            );
        }

        if ($storagePageId <= 0) {
            throw new InvalidConfigurationException(
                $exceptionMessage,
                1794745539
            );
        }

        return $storagePageId;
    }

    /**
     * @param int<1,max> $uid
     */
    private function updatePasswordHashInDatabase(
        string $table,
        int $uid,
        string $newHash,
    ): void {
        $connection = $this->connectionPool->getConnectionForTable($table);
        $connection->update(
            $table,
            [self::PASSWORD_COLUMN_NAME => $newHash],
            ['uid' => $uid]
        );
        $this->logger?->notice('Automatic api key hash update for user record in {table} with uid {uid}', [
            'table' => $table,
            'uid'   => $uid,
        ]);
    }

    private function performFakePasswordCheck(
        #[SensitiveParameter]
        string $password,
    ): void {
        $passwordHashInDatabase = '$argon2i$v=19$m=65536,t=16,p=1$RTIvckx5M1YzM1NSUWtmWQ$iRSp7Z7joyWKFuJvvTmLkPDtI/TsUwEl7IHWSCRVgqo';
        try {
            $hashInstance = $this->passwordHashFactory->get(
                $passwordHashInDatabase,
                'FE'
            );
        } catch (InvalidPasswordHashException $exception) {
            $this->logger?->critical(
                'Could not get Password Hash instance for enumeration mitigation'
            );

            return;
        }

        $hashInstance->checkPassword($password, $passwordHashInDatabase);
    }
}
