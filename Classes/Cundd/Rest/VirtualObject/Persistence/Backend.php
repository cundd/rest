<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 16:30
 */

namespace Cundd\Rest\VirtualObject\Persistence;


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
		$this->getAdapter()->exec_INSERTquery($tableName, $row);
		$uid = $this->getAdapter()->sql_insert_id();
		$this->checkSqlErrors();
		return (integer) $uid;
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
		$result = $this->getAdapter()->exec_DELETEquery($tableName, $this->createWhereStatementFromQuery($identifier, $tableName));
		$this->checkSqlErrors();
		return $result;
	}

	/**
	 * Returns the number of items matching the query
	 *
	 * @param string $tableName The database table name
	 * @param array  $query
	 * @return integer
	 * @api
	 */
	public function getObjectCountByQuery($tableName, $query) {
		list($row) = $this->getAdapter()->exec_SELECTgetRows('COUNT(*) AS count', $tableName, $this->createWhereStatementFromQuery($query, $tableName));
		$this->checkSqlErrors();
		return intval($row['count']);
	}

	/**
	 * Returns the object data matching the $query
	 *
	 * @param string $tableName The database table name
	 * @param array  $query
	 * @return array
	 * @api
	 */
	public function getObjectDataByQuery($tableName, $query) {
		$result = $this->getAdapter()->exec_SELECTgetRows('*', $tableName, $this->createWhereStatementFromQuery($query, $tableName));
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
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException($error, 1247602160);
		}
	}

	/**
	 * Creates the WHERE-statement from the given key-value query-array
	 *
	 * @param array  $query
	 * @param string $tableName
	 * @throws Exception\InvalidColumnNameException if one of the column names is invalid
	 * @throws Exception\InvalidTableNameException if the table name is invalid
	 * @return string
	 */
	protected function createWhereStatementFromQuery($query, $tableName) {
		if (!is_string($tableName)) {
			throw new InvalidTableNameException('The given table name is of type ' . gettype($tableName) . '. You may have a wrong argument order', 1395677889);
		}
		if (!$tableName) {
			throw new InvalidTableNameException('The given table name is empty', 1395677890);
		}


		$adapter = $this->getAdapter();
		$constraints = array();
		foreach ($query as $column => $value) {
			if (!ctype_alnum(str_replace('_', '', $column))) {
				throw new InvalidColumnNameException('The given column is not valid', 1395678424);
			}

			$constraints[] = ''
				. $column
				. '='
				. $adapter->fullQuoteStr($value, $tableName)
			;
		}
		return implode(' AND ', $constraints);
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