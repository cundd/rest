<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 16:24
 */

namespace Cundd\Rest\VirtualObject\Persistence;

/**
 * Interface for the database backend
 *
 * @package Cundd\Rest\VirtualObject\Persistence
 */
interface BackendInterface {
	/**
	 * Adds a row to the storage
	 *
	 * @param string $tableName The database table name
	 * @param array $row The row to insert
	 * @return integer the UID of the inserted row
	 */
	public function addRow($tableName, array $row);

	/**
	 * Updates a row in the storage
	 *
	 * @param string $tableName The database table name
	 * @param array  $query
	 * @param array  $row       The row to update
	 * @return mixed|void
	 */
	public function updateRow($tableName, $query, array $row);

	/**
	 * Deletes a row in the storage
	 *
	 * @param string $tableName The database table name
	 * @param array $identifier An array of identifier array('fieldname' => value). This array will be transformed to a WHERE clause
	 * @return mixed|void
	 */
	public function removeRow($tableName, array $identifier);

	/**
	 * Returns the number of items matching the query
	 *
 	 * @param string $tableName The database table name
	 * @param array $query
	 * @return integer
	 * @api
	 */
	public function getObjectCountByQuery($tableName, $query);

	/**
	 * Returns the object data matching the $query
	 *
	 * @param string $tableName The database table name
	 * @param array $query
	 * @return array
	 * @api
	 */
	public function getObjectDataByQuery($tableName, $query);
} 