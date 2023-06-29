<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\VirtualObject\Backend;

use Cundd\Rest\VirtualObject\Persistence\Backend\DoctrineBackend;
use Cundd\Rest\VirtualObject\Persistence\Backend\WhereClauseBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DoctrineBackendTest extends AbstractBackendTest
{
    public function setUp(): void
    {
        parent::setUp();
        if (!class_exists(ConnectionPool::class)) {
            $this->markTestSkipped('Doctrine is not used');
        } else {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class);
            $this->fixture = new DoctrineBackend($connection, new WhereClauseBuilder());
        }
    }
}
