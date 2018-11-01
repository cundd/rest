<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\Persistence\Backend\DoctrineBackend;
use Cundd\Rest\VirtualObject\Persistence\Backend\V7Backend;
use Cundd\Rest\VirtualObject\Persistence\Backend\WhereClauseBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Backend implements BackendInterface
{
    /**
     * @var BackendInterface
     */
    private $concreteBackend;

    /**
     * Backend constructor.
     *
     * @param BackendInterface $concreteBackend
     */
    public function __construct(BackendInterface $concreteBackend = null)
    {
        if ($concreteBackend) {
            $this->concreteBackend = $concreteBackend;
        } elseif ($this->getUseV7Backend()) {
            $this->concreteBackend = new V7Backend($GLOBALS['TYPO3_DB']);
        } else {
            /** @var ConnectionPool $connection */
            $connection = GeneralUtility::makeInstance(ConnectionPool::class);

            $this->concreteBackend = new DoctrineBackend($connection, new WhereClauseBuilder());
        }
    }

    public function addRow($tableName, array $row)
    {
        return $this->concreteBackend->addRow($tableName, $row);
    }

    public function updateRow($tableName, array $identifier, array $row)
    {
        return $this->concreteBackend->updateRow($tableName, $identifier, $row);
    }

    public function removeRow($tableName, array $identifier)
    {
        return $this->concreteBackend->removeRow($tableName, $identifier);
    }

    public function getObjectCountByQuery($tableName, $query)
    {
        return $this->concreteBackend->getObjectCountByQuery($tableName, $query);
    }

    public function getObjectDataByQuery($tableName, $query)
    {
        return $this->concreteBackend->getObjectDataByQuery($tableName, $query);
    }

    /**
     * @return bool
     */
    private function getUseV7Backend()
    {
        if (!isset($GLOBALS['TYPO3_DB'])) {
            return false;
        }

        $database = $GLOBALS['TYPO3_DB'];

        return is_object($database) && $database instanceof \TYPO3\CMS\Core\Database\DatabaseConnection;
    }
}
