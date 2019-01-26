<?php
declare(strict_types=1);

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
     * @param string $tableName Database table name
     * @param array  $row       Data to insert
     * @return int the UID of the inserted row
     * @throws InvalidTableNameException if the table name is not valid
     * @throws SqlErrorException on SQL errors
     */
    public function addRow(string $tableName, array $row): int;

    /**
     * Updates a row in the storage
     *
     * @param string $tableName  Database table name
     * @param array  $identifier A map of key value pairs to identify the record to update
     * @param array  $row        Data to update the row
     * @return int the number of affected rows
     */
    public function updateRow(string $tableName, array $identifier, array $row): int;

    /**
     * Deletes a row in the storage
     *
     * @param string $tableName  Database table name
     * @param array  $identifier A map of key value pairs to identify the record to update
     * @return int the number of affected rows
     * @throws InvalidTableNameException if the table name is not valid
     * @throws InvalidOperatorException if the where clause could not be built
     * @throws SqlErrorException on SQL errors
     */
    public function removeRow(string $tableName, array $identifier): int;

    /**
     * Returns the number of items matching the query
     *
     * @param string         $tableName Database table name
     * @param QueryInterface $query     A Query instance to construct the WHERE clause
     * @return integer
     * @throws InvalidTableNameException if the table name is not valid
     * @throws InvalidOperatorException if the where clause could not be built
     * @throws SqlErrorException on SQL errors
     */
    public function getObjectCountByQuery(string $tableName, QueryInterface $query): int;

    /**
     * Returns the object data matching the $query
     *
     * @param string         $tableName Database table name
     * @param QueryInterface $query     A Query instance to construct the WHERE clause
     * @return array
     * @throws InvalidTableNameException if the table name is not valid
     * @throws InvalidOperatorException if the where clause could not be built
     * @throws SqlErrorException on SQL errors
     */
    public function getObjectDataByQuery(string $tableName, QueryInterface $query): array;
}
