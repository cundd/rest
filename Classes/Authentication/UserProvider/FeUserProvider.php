<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication\UserProvider;

use Cundd\Rest\Authentication\UserProviderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * User Provider implementation for FeUsers
 */
class FeUserProvider implements UserProviderInterface
{
    /**
     * Name of the password column
     */
    public const PASSWORD_COLUMN_NAME = 'tx_rest_apikey';

    /**
     * Returns if the user with the given credentials is valid
     */
    public function checkCredentials(string $username, string $password): bool
    {
        if ('' === $username || '' === $password) {
            return false;
        }

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('fe_users');

        return 0 !== $queryBuilder
            ->count('*')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq(
                    'username',
                    $queryBuilder->createNamedParameter($username)
                ),
                $queryBuilder->expr()->eq(
                    self::PASSWORD_COLUMN_NAME,
                    $queryBuilder->createNamedParameter($password)
                )
            )
            ->executeQuery()
            ->fetchOne();
    }
}
