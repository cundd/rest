<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
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
