<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\Persistence\Backend\DoctrineBackend;
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
    public static function getBackend(): BackendInterface
    {
        /** @var ConnectionPool $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class);

        return new DoctrineBackend($connection, new WhereClauseBuilder());
    }
}
