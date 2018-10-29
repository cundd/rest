<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\Exception\InvalidOperatorException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidTableNameException;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException;

/**
 * Interface for the database backend
 */
interface BackendInterface
{
    /**
     * Adds a row to the storage
     *
     * @param string $tableName The database table name
     * @param array  $row       The row to insert
     * @return integer the UID of the inserted row
     * @throws InvalidTableNameException if the table name is not valid
     * @throws SqlErrorException on SQL errors
     */
    public function addRow($tableName, array $row);

    /**
     * Updates a row in the storage
     *
     * @param string               $tableName The database table name
     * @param array|QueryInterface $query     A Query instance or a map of key value pairs to construct the WHERE clause
     * @param array                $row       The row to update
     * @return mixed|void
     * @throws InvalidTableNameException if the table name is not valid
     * @throws InvalidOperatorException if the where clause could not be built
     * @throws SqlErrorException on SQL errors
     */
    public function updateRow($tableName, $query, array $row);

    /**
     * Deletes a row in the storage
     *
     * @param string $tableName The database table name
     * @param array  $query     A map of key value pairs to construct the WHERE clause
     * @return mixed|void
     * @throws InvalidTableNameException if the table name is not valid
     * @throws InvalidOperatorException if the where clause could not be built
     * @throws SqlErrorException on SQL errors
     */
    public function removeRow($tableName, array $query);

    /**
     * Returns the number of items matching the query
     *
     * @param string               $tableName The database table name
     * @param array|QueryInterface $query     A Query instance or a map of key value pairs to construct the WHERE clause
     * @return integer
     * @throws InvalidTableNameException if the table name is not valid
     * @throws InvalidOperatorException if the where clause could not be built
     * @throws SqlErrorException on SQL errors
     * @api
     */
    public function getObjectCountByQuery($tableName, $query);

    /**
     * Returns the object data matching the $query
     *
     * @param string               $tableName The database table name
     * @param array|QueryInterface $query     A Query instance or a map of key value pairs to construct the WHERE clause
     * @return array
     * @throws InvalidTableNameException if the table name is not valid
     * @throws InvalidOperatorException if the where clause could not be built
     * @throws SqlErrorException on SQL errors
     * @api
     */
    public function getObjectDataByQuery($tableName, $query);
}
