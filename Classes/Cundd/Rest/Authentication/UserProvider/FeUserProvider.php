<?php
/*
 * The MIT License (MIT)
 * 
 * Copyright (c) 2013 Daniel Corn
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
 
/*
 * rest
 * @author daniel
 * Date: 14.09.13
 * Time: 18:58
 */
  


namespace Cundd\Rest\Authentication\UserProvider;


use Cundd\Rest\Authentication\UserProviderInterface;

class FeUserProvider implements UserProviderInterface {
	/**
	 * Returns if the user with the given credentials is valid
	 *
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function checkCredentials($username, $password) {
		/** @var \t3lib_DB $databaseAdapter */
		$databaseAdapter = $this->getDatabaseAdapter();

		$whereClause = $this->buildWhereStatement(array(
			'username' => $username,
			'password' => $password
		));
		$result = $databaseAdapter->exec_SELECTquery('COUNT(*)', 'fe_users', $whereClause);
		$row = $databaseAdapter->sql_fetch_row($result);
		return (bool) $row[0];
	}

	/**
	 * Builds the where statement from the given properties
	 * @param array $properties
	 * @return string
	 */
	protected function buildWhereStatement($properties) {
		$whereParts = array();
		$databaseAdapter = $this->getDatabaseAdapter();
		foreach($properties as $key => $value) {
			if ($key === 'password') {
				list($value, $key) = $this->preparePassword($value);
			}
			$whereParts[] = '`' . $key . '`=' . $databaseAdapter->fullQuoteStr($value, 'fe_users');
		}
		return implode(' AND ', $whereParts);
	}

	/**
	 * Returns an array containing the prepared password and table column
	 * @param string $password
	 * @return array
	 */
	protected function preparePassword($password) {
		return array($password, 'tx_rest_apikey');
	}

	/**
	 * Returns the database adapter
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseAdapter() {
		return $GLOBALS['TYPO3_DB'];
	}
}