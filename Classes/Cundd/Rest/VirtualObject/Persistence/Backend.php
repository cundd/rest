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

use Cundd\Rest\Exception;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidColumnNameException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidTableNameException;

class Backend implements BackendInterface {
	/**
	 * Adds a row to the storage
	 *
	 * @param string $tableName The database table name
	 * @param array  $row       The row to insert
	 * @return integer the UID of the inserted row
	 */
	public function addRow($tableName, array $row) {
		$this->checkTableArgument($tableName);

		$this->getAdapter()->exec_INSERTquery($tableName, $row);
		$uid = $this->getAdapter()->sql_insert_id();
		$this->checkSqlErrors();
		return (integer)$uid;
	}

	/**
	 * Updates a row in the storage
	 *
	 * @param string $tableName The database table name
	 * @param array  $query
	 * @param array  $row       The row to update
	 * @return mixed|void
	 */
	public function updateRow($tableName, $query, array $row) {
		$this->checkTableArgument($tableName);

		$result = $this->getAdapter()->exec_UPDATEquery($tableName, $this->createWhereStatementFromQuery($query, $tableName), $row);
		$this->checkSqlErrors();
		return $result;
	}

	/**
	 * Deletes a row in the storage
	 *
	 * @param string $tableName  The database table name
	 * @param array  $identifier An array of identifier array('fieldname' => value). This array will be transformed to a WHERE clause
	 * @return mixed|void
	 */
	public function removeRow($tableName, array $identifier) {
		$this->checkTableArgument($tableName);

		$result = $this->getAdapter()->exec_DELETEquery($tableName, $this->createWhereStatementFromQuery($identifier, $tableName));
		$this->checkSqlErrors();
		return $result;
	}

	/**
	 * Returns the number of items matching the query
	 *
	 * @param string $tableName The database table name
	 * @param QueryInterface|array  $query
	 * @return integer
	 * @api
	 */
	public function getObjectCountByQuery($tableName, $query) {
		$this->checkTableArgument($tableName);

		list($row) = $this->getAdapter()->exec_SELECTgetRows(
			'COUNT(*) AS count',
			$tableName,
			$this->createWhereStatementFromQuery($query, $tableName),
			'',
			$this->createOrderingStatementFromQuery($query),
			$this->createLimitStatementFromQuery($query)
		);
		$this->checkSqlErrors();
		return intval($row['count']);
	}

	/**
	 * Returns the object data matching the $query
	 *
	 * @param string $tableName The database table name
	 * @param QueryInterface|array  $query
	 * @return array
	 * @api
	 */
	public function getObjectDataByQuery($tableName, $query) {
		$this->checkTableArgument($tableName);

		$result = $this->getAdapter()->exec_SELECTgetRows(
			'*',
			$tableName,
			$this->createWhereStatementFromQuery($query, $tableName),
			'',
			$this->createOrderingStatementFromQuery($query),
			$this->createLimitStatementFromQuery($query)
		);
		$this->checkSqlErrors();
		return $result;
	}

	/**
	 * Checks if there are SQL errors in the last query, and if yes, throw an exception.
	 *
	 * @return void
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException
	 */
	protected function checkSqlErrors() {
		$error = $this->getAdapter()->sql_error();
		if ($error !== '') {
			$error = '#' . $this->getAdapter()->sql_errno() . ': ' . $error;
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException($error, 1247602160);
		}
	}

	/**
	 * Creates the WHERE-statement from the given key-value query-array
	 *
	 * @param QueryInterface|array $query
	 * @param string               $tableName
	 * @throws Exception\InvalidColumnNameException if one of the column names is invalid
	 * @throws Exception\InvalidTableNameException if the table name is invalid
	 * @return string
	 */
	protected function createWhereStatementFromQuery($query, $tableName) {
		$this->checkTableArgument($tableName);

		if ($query instanceof QueryInterface) {
			$query = $query->getConstraint();
		}

		$adapter     = $this->getAdapter();
		$constraints = array();
		foreach ($query as $column => $value) {
			if (!ctype_alnum(str_replace('_', '', $column))) {
				throw new InvalidColumnNameException('The given column is not valid', 1395678424);
			}

			$constraints[] = ''
				. $column
				. '='
				. $adapter->fullQuoteStr($value, $tableName);
		}
		return implode(' AND ', $constraints);
	}

	/**
	 * Returns the offset and limit statement for the given query
	 *
	 * @param QueryInterface $query
	 * @return string
	 */
	protected function createLimitStatementFromQuery($query) {
		#SELECT * FROM tbl LIMIT 5,10;  # Retrieve rows 6-15
		if ($query instanceof QueryInterface) {
			$limit = '' . $query->getOffset();
			if ($query->getLimit()) {
				$limit = ($limit ? $limit : '0') . ',' . $query->getLimit();
			}
			return $limit;
		}
		return '';
	}

	/**
	 * Returns the order by statement for the given query
	 *
	 * @param QueryInterface $query
	 * @return string
	 */
	protected function createOrderingStatementFromQuery($query) {
		if ($query instanceof QueryInterface) {
			$orderings = $query->getOrderings();
			$orderArray = array_map(function($property, $direction) {
				return $property . ' ' . $direction;
			}, array_keys($orderings), $orderings);
			return implode(', ', $orderArray);
		}
		return '';
	}

	/**
	 * Checks if the given table name is valid
	 *
	 * @param string $tableName
	 * @throws Exception\InvalidTableNameException
	 */
	protected function checkTableArgument($tableName) {
		if (!is_string($tableName)) {
			throw new InvalidTableNameException('The given table name is of type ' . gettype($tableName) . '. You may have a wrong argument order', 1395677889);
		}
		if (!$tableName) {
			throw new InvalidTableNameException('The given table name is empty', 1395677890);
		}
		if (!ctype_alnum(str_replace('_', '', $tableName))) {
			throw new InvalidTableNameException('The given table name is not valid', 1395682370);
		}
	}

	/**
	 * Returns the database adapter
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	public function getAdapter() {
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		return $GLOBALS['TYPO3_DB'];
	}


}
