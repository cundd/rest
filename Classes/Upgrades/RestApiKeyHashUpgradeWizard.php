<?php

declare(strict_types=1);

namespace Cundd\Rest\Upgrades;

use Cundd\Rest\Authentication\UserProvider\FeUserProvider;
use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Upgrades\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;

/**
 * Upgrade wizard to hash the API key of all records in the `fe_users` table
 */
#[UpgradeWizard('rest_apiKeyHashUpgradeWizard')]
readonly class RestApiKeyHashUpgradeWizard implements UpgradeWizardInterface
{
    private const TABLE = 'fe_users';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly PasswordHashFactory $passwordHashFactory,
    ) {
    }

    public function getTitle(): string
    {
        return sprintf('Hash API keys of "%s"', self::TABLE);
    }

    public function getDescription(): string
    {
        $recordsWhichNeedAnUpdate = $this->getCountOfRecordsWhichNeedAnUpdate();

        return sprintf(
            'Hash the API key column "%s" of table "%s" for %d records which do not contain a valid password hash yet.',
            FeUserProvider::PASSWORD_COLUMN_NAME,
            self::TABLE,
            $recordsWhichNeedAnUpdate
        );
    }

    public function updateNecessary(): bool
    {
        return $this->getCountOfRecordsWhichNeedAnUpdate() > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function executeUpdate(): bool
    {
        $defaultHashInstance = $this->passwordHashFactory
            ->getDefaultHashInstance('FE');

        foreach ($this->getUsersWithApiKey() as $user) {
            $storedApiKey = $user[FeUserProvider::PASSWORD_COLUMN_NAME];
            assert('' !== $storedApiKey && null !== $storedApiKey);
            if (!$this->isHashMissing($storedApiKey)) {
                continue;
            }

            $hashedApiKey = $defaultHashInstance->getHashedPassword($storedApiKey);
            if (null === $hashedApiKey) {
                continue;
            }

            $this->connectionPool->getConnectionForTable(self::TABLE)->update(
                self::TABLE,
                [FeUserProvider::PASSWORD_COLUMN_NAME => $hashedApiKey],
                ['uid' => (int) $user['uid']]
            );
        }

        return true;
    }

    private function getCountOfRecordsWhichNeedAnUpdate(): int
    {
        return count(array_filter(
            $this->getUsersWithApiKey(),
            function (array $user) {
                $storedApiKey = $user[FeUserProvider::PASSWORD_COLUMN_NAME];
                assert('' !== $storedApiKey && null !== $storedApiKey);

                return $this->isHashMissing($storedApiKey);
            },
        ));
    }

    private function isHashMissing(string $storedApiKey): bool
    {
        try {
            $this->passwordHashFactory->get($storedApiKey, 'FE');
        } catch (InvalidPasswordHashException) {
            // The stored value is not a valid salted hash (yet), so it is a
            // plain API key that needs to be hashed
            return true;
        }

        // The stored value is a valid salted hash already, so no update is needed
        return false;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function getUsersWithApiKey(): array
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->select('uid', FeUserProvider::PASSWORD_COLUMN_NAME)
            ->from(self::TABLE)
            ->where($queryBuilder->expr()->neq(
                FeUserProvider::PASSWORD_COLUMN_NAME,
                $queryBuilder->createNamedParameter('')
            ))
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
