<?php
namespace Cundd\Rest\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Daniel Corn <cod@iresults.li>, iresults
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
 ***************************************************************/

use Cundd\Rest\Domain\Exception\InvalidDatabaseNameException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class Document
 *
 * A Document is a flexible, schema-less object
 *
 * @package rest
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Document extends AbstractEntity implements \ArrayAccess {
	/**
	 * Name of the property that holds the data
	 */
	const DATA_PROPERTY_NAME = 'dataProtected';

	/**
	 * ID
	 *
	 * @var \string
	 * @validate NotEmpty
	 * @identity
	 */
	protected $id;

	/**
	 * Database
	 *
	 * @var \string
	 * @validate NotEmpty
	 * @identity
	 */
	protected $db;

	/**
	 * Document data
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $dataProtected;

	/**
	 * Unpacked Document content
	 *
	 * @var array
	 */
	protected $_dataUnpacked = NULL;

	/**
	 * Returns the Documents global unique identifier
	 *
	 * @return string
	 */
	public function getGuid() {
		$guid = $this->db . '-' . $this->id;
		return $guid !== '-' ? $guid : NULL;
	}

	/**
	 * Sets the Document's ID
	 *
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * Returns the Document's ID
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Sets the Document's data
	 *
	 * @param string $content
	 */
	public function _setDataProtected($content) {
		$this->dataProtected = $content;
		$this->_dataUnpacked = NULL;
	}

	/**
	 * Returns the Document's data
	 *
	 * @return string
	 */
	public function _getDataProtected() {
		return $this->dataProtected;
	}

	/**
	 * Sets the Document's database
	 *
	 * @param string $db
	 * @throws InvalidDatabaseNameException if the given database name is not valid
	 */
	public function _setDb($db) {
		if (!ctype_alnum($db)) throw new InvalidDatabaseNameException('The given database name is invalid', 1389258923);
		$this->db = strtolower($db);
	}

	/**
	 * Returns the Document's database
	 *
	 * @return string
	 */
	public function _getDb() {
		return $this->db;
	}

	/**
	 * Returns the value for the given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function valueForKey($key) {
		return $this->_valueForKey($key);
	}

	/**
	 * Returns the value for the given key path (i.e. "foo.bar")
	 *
	 * @param string $keyPath
	 * @return mixed
	 */
	public function valueForKeyPath($keyPath) {
		if (strpos($keyPath, '.') === FALSE) {
			return $this->valueForKey($keyPath);
		}
		return ObjectAccess::getPropertyPath($this->_getUnpackedData(), $keyPath);
	}

	/**
	 * Invoked if a retrieved key is not defined
	 *
	 * @param string $key
	 * @return null
	 */
	public function valueForUndefinedKey($key) {
		return NULL;
	}

	/**
	 * Returns the value for the given key or invokes valueForUndefinedKey() if
	 * the key is not set
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function _valueForKey($key) {
		if (isset($this->$key) && $this->$key) {
			return $this->$key;
		}

		$unpackedContent = $this->_getUnpackedData();
		if (isset($unpackedContent[$key])) {
			return $unpackedContent[$key];
		} else if (property_exists($this, $key)) {
			return NULL;
		}
		return $this->valueForUndefinedKey($key);
	}

	/**
	 * Sets the value for the given key
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return $this
	 */
	public function setValueForKey($key, $value) {
		$this->_setValueForKey($key, $value);
		return $this;
	}

//	/**
//	 * Sets the value for the given key path
//	 *
//	 * @param mixed $value
//	 * @param string $keyPath
//	 * @return $this
//	 */
//	public function setValueForKeyPath($value, $keyPath) {
//		$lastDotPosition = strrpos($keyPath, '.');
//		$keyPathToSubject = substr($keyPath, 0, $lastDotPosition);
//		$key = substr($keyPath, $lastDotPosition + 1);
//		$subject = ObjectAccess::getProperty($this, $keyPathToSubject);
//		ObjectAccess::setProperty($subject, $key, $value);
//		return $this;
//	}

	/**
	 * Sets the value for the given key
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	protected function _setValueForKey($key, $value) {
		if ($key === 'dataProtected') {
			$this->dataProtected = $value;
			$this->_dataUnpacked = NULL;
			return;
		}
		if (property_exists($this, $key)) {
			$this->$key = $value;
			return;
		}


		$unpackedContent = $this->_getUnpackedData();
		$unpackedContent[$key] = $value;

		unset($this->_dataUnpacked);
		$this->_dataUnpacked = $unpackedContent;
		$this->_packContent();
	}

	/**
	 * Returns the unpacked Document data
	 *
	 * @return array|mixed
	 */
	public function _getUnpackedData() {
		if (!$this->_dataUnpacked) {
			$this->_dataUnpacked = json_decode($this->dataProtected, TRUE);
		}
		return $this->_dataUnpacked;
	}

	/**
	 * Packs the Document content
	 *
	 * @return $this
	 */
	public function _packContent() {
		$this->dataProtected = json_encode($this->_dataUnpacked);
		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 * @return boolean true on success or false on failure.
	 *                      </p>
	 *                      <p>
	 *                      The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		return $this->valueForKey($offset) ? TRUE : FALSE;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->valueForKey($offset);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->setValueForKey($offset, $value);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->setValueForKey($offset, NULL);
	}

	function __get($name) {
		return $this->valueForKey($name);
	}

	function __isset($name) {
		return $this->offsetExists($name);
	}


}
?>