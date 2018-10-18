<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\Persistence\Backend\DoctrineBackend;
use Cundd\Rest\VirtualObject\Persistence\Backend\V7Backend;
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
        } elseif (isset($GLOBALS['TYPO3_DB']) && is_object($GLOBALS['TYPO3_DB'])) {
            $this->concreteBackend = new V7Backend($GLOBALS['TYPO3_DB']);
        } else {
            /** @var ConnectionPool $connection */
            $connection = GeneralUtility::makeInstance(ConnectionPool::class);

            $this->concreteBackend = new DoctrineBackend($connection);
        }
    }


    /**
     * Adds a row to the storage
     *
     * @param string $tableName The database table name
     * @param array  $row       The row to insert
     * @return integer the UID of the inserted row
     */
    public function addRow($tableName, array $row)
    {
        return $this->concreteBackend->addRow($tableName, $row);
    }

    /**
     * Updates a row in the storage
     *
     * @param string $tableName The database table name
     * @param array  $query
     * @param array  $row       The row to update
     * @return mixed
     */
    public function updateRow($tableName, $query, array $row)
    {
        return $this->concreteBackend->updateRow($tableName, $query, $row);
    }

    /**
     * Deletes a row in the storage
     *
     * @param string $tableName  The database table name
     * @param array  $identifier An array of identifier array('fieldname' => value). This array will be transformed to a WHERE clause
     * @return mixed
     */
    public function removeRow($tableName, array $identifier)
    {
        return $this->concreteBackend->removeRow($tableName, $identifier);
    }

    /**
     * Returns the number of items matching the query
     *
     * @param string               $tableName The database table name
     * @param QueryInterface|array $query
     * @return integer
     * @api
     */
    public function getObjectCountByQuery($tableName, $query)
    {
        return $this->concreteBackend->getObjectCountByQuery($tableName, $query);
    }

    /**
     * Returns the object data matching the $query
     *
     * @param string               $tableName The database table name
     * @param QueryInterface|array $query
     * @return array
     * @api
     */
    public function getObjectDataByQuery($tableName, $query)
    {
        return $this->concreteBackend->getObjectDataByQuery($tableName, $query);
    }
}
