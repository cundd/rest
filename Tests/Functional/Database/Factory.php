<?php

namespace Cundd\Rest\Tests\Functional\Database;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Factory
{
    /**
     * @return DatabaseConnectionInterface
     */
    public static function getConnection()
    {
        if (false === isset($GLOBALS['TYPO3_DB']) || false === is_object($GLOBALS['TYPO3_DB'])) {
            /** @var ConnectionPool $connection */
            $connection = GeneralUtility::makeInstance(ConnectionPool::class);

            return new DoctrineConnection($connection);
        }

        $storedAdapter = $GLOBALS['TYPO3_DB'];
        if ($storedAdapter instanceof DatabaseConnectionInterface) {
            return $storedAdapter;
        }

        return new V7Connection($storedAdapter);
    }
}
