<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\Persistence\Backend\DoctrineBackend;
use Cundd\Rest\VirtualObject\Persistence\Backend\V7Backend;
use Cundd\Rest\VirtualObject\Persistence\Backend\WhereClauseBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class BackendFactory
{
    /**
     * Return the Backend
     *
     * @return BackendInterface|RawQueryBackendInterface
     */
    public static function getBackend()
    {
        if (false === static::getUseV7Backend()) {
            /** @var ConnectionPool $connection */
            $connection = GeneralUtility::makeInstance(ConnectionPool::class);

            return new DoctrineBackend($connection, new WhereClauseBuilder());
        } else {
            return new V7Backend($GLOBALS['TYPO3_DB']);
        }
    }

    /**
     * @return bool
     */
    private static function getUseV7Backend()
    {
        if (!isset($GLOBALS['TYPO3_DB'])) {
            return false;
        }

        $database = $GLOBALS['TYPO3_DB'];

        return is_object($database) && $database instanceof \TYPO3\CMS\Core\Database\DatabaseConnection;
    }
}
