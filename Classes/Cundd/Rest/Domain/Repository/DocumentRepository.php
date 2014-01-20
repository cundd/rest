<?php
namespace Cundd\Rest\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andreas Thurnheer-Meier <tma@iresults.li>, iresults
 *  Daniel Corn <cod@iresults.li>, iresults
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

use Cundd\Rest\Domain\Exception\InvalidDocumentException;
use Cundd\Rest\Domain\Model\Document;
use Cundd\Rest\Domain\Exception\InvalidDatabaseNameException;
use Cundd\Rest\Domain\Exception\NoDatabaseSelectedException;
use Iresults\Core\Iresults;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 *
 *
 * @package rest
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DocumentRepository extends Repository {
	/**
	 * Currently selected database
	 *
	 * @var string
	 */
	protected $database = '';

	/**
	 * Defines if query results should be retrieved raw and converted by
	 * convertCollection()
	 *
	 * @var bool
	 */
	protected $useRawQueryResults = FALSE;

	/**
	 * Selects a database
	 *
	 * @param string $database
	 * @throws InvalidDatabaseNameException if an invalid database name is provided
	 */
	public function setDatabase($database) {
		if (!ctype_alnum($database)) throw new InvalidDatabaseNameException('The given database name contains invalid characters', 1389258923);
		if (strtolower($database) !== $database) throw new InvalidDatabaseNameException('The given database name must be lowercase', 1389348390);
		$this->database = $database;
	}

	/**
	 * Returns the currently selected database
	 *
	 * @return string
	 */
	public function getDatabase() {
		return $this->database;
	}

	/**
	 * Gets/sets the current database
	 *
	 * @return string
	 */
	public function database() {
		if (func_num_args() > 0) {
			$this->setDatabase(func_get_arg(0));
		}
		return $this->getDatabase();
	}

	/**
	 * Registers the given Document
	 *
	 * This is a high level shorthand for:
	 * Object exists?
	 *    Yes -> update
	 *    No -> add
	 *
	 * @param Document|array|\stdClass $data
	 * @throws \Cundd\Rest\Domain\Exception\InvalidDocumentException if the given data could not be converted to a Document
	 * @return Document Returns the registered Document
	 */
	public function register($data) {
		if (is_object($data) && $data instanceof Document) {
			$this->registerObject($data);
			return $data;
		} else {
			return $this->registerData($data);
		}
	}

	/**
	 * Registers the given Document
	 *
	 * This is a high level shorthand for:
	 * Object exists?
	 *    Yes -> update
	 *    No -> add
	 *
	 * @param array $data
	 * @throws \Cundd\Rest\Domain\Exception\InvalidDocumentException if the given data could not be converted to a Document
	 * @return Document Returns the registered Document
	 */
	public function registerData($data) {
		$object = $this->convertToDocument($data);
		if (!$object) {
			throw new InvalidDocumentException('Could not convert the given data to a Document', 1389286531);
		}
		$this->registerObject($object);
		return $object;
	}

	/**
	 * Registers the given Document
	 *
	 * This is a high level shorthand for:
	 * Object exists?
	 *    Yes -> update
	 *    No -> add
	 *
	 * @param Document $object
	 * @return Document Returns the registered Document
	 */
	public function registerObject($object) {
		$foundObject = $this->findByGuid($object->getGuid());

		// If the object appears as new but has a matching object merge those
		if ($this->persistenceManager->isNewObject($object) && $foundObject) {
			$object = $this->mergeDocuments($foundObject, $object);
		}

		Iresults::pd('register', $object, ($object->_isNew() || $this->useRawQueryResults), ($this->persistenceManager->isNewObject($object) || $this->useRawQueryResults));

		//if ($this->persistenceManager->isNewObject($object) || $this->useRawQueryResults) {
		if ($object->_isNew() || $this->useRawQueryResults) {
			$this->add($object);
		} else {
			$this->update($object);
		}
		return $object;
	}

	/**
	 * Adds an object to this repository
	 *
	 * @param Document $object The object to add
	 * @throws NoDatabaseSelectedException if the given object and the repository have no database set
	 * @return void
	 * @api
	 */
	public function add($object) {
		if (!$object->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$object->_setDb($currentDatabase);
		}
		$this->willChangeDocument($object);
		parent::add($object);
		$this->didChangeDocument($object);
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param Document $object The object to remove
	 * @throws NoDatabaseSelectedException if the given object and the repository have no database set
	 * @return void
	 * @api
	 */
	public function remove($object) {
		if (!$object->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$object->_setDb($currentDatabase);
		}
		$this->willChangeDocument($object);
		parent::remove($object);
		$this->didChangeDocument($object);
	}

	/**
	 * Replaces an existing object with the same identifier by the given object
	 *
	 * @param Document $modifiedObject The modified object
	 * @throws NoDatabaseSelectedException if the given object and the repository have no database set
	 * @return void
	 * @api
	 */
	public function update($modifiedObject) {
		if (!$modifiedObject->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$modifiedObject->_setDb($currentDatabase);
		}
		$this->willChangeDocument($modifiedObject);
		parent::update($modifiedObject);
		$this->didChangeDocument($modifiedObject);
	}

	/**
	 * Returns all objects of the selected Document database
	 *
	 * @throws NoDatabaseSelectedException if no database has been selected
	 * @return array<Document>
	 * @api
	 */
	public function findAll() {
		$currentDatabase = $this->getDatabase();
		if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);

		$query = $this->createQuery();
		$query->matching($query->equals('db', $currentDatabase));
		return $this->convertCollection($query->execute());
	}

	/**
	 * Returns all objects of the given Document database
	 *
	 * Will select the given Document database and call findAll()
	 *
	 * @param string $database
	 * @return array<Document>
	 */
	public function findByDatabase($database) {
		$this->setDatabase($database);
		return $this->findAll();
	}

	/**
	 * Returns the Document with the given GUID
	 *
	 * @param string $guid
	 * @return Document
	 */
	public function findByGuid($guid) {
		/** @var Query $query */
		$query = $this->createQuery();
		list($database, $id) = explode('-', $guid, 2);
		$query->matching(
			$query->logicalAnd(
				$query->equals('db', $database),
				$query->equals('id', $id)
			)
		);

		$query->setLimit(1);

		$result = $this->convertCollection($query->execute());
		Iresults::pd($result);
		if (!$result) {
			return NULL;
		}
		if ($this->useRawQueryResults) {
			return reset($result);
		}
		if ($result instanceof QueryResultInterface) {
			return $result->getFirst();
		}
		return NULL;
	}

	/**
	 * Returns the Document with the given ID
	 *
	 * @param string $id
	 * @return Document
	 */
	public function findOneById($id) {
		$query = $this->createQuery();
		$query->matching($query->equals('id', $id));

		$result = $this->convertCollection($query->execute());
		if (!$result) {
			return NULL;
		}
		if ($this->useRawQueryResults) {
			return reset($result);
		}
		if ($result instanceof QueryResultInterface) {
			return $result->getFirst();
		}
	}

	/**
	 * @see findOneById()
	 */
	public function findById($id) {
		return $this->findOneById($id);
	}

	/**
	 * Returns all objects ignoring the selected database
	 *
	 * @return array<Document>
	 * @api
	 */
	public function findAllIgnoreDatabase() {
		return $this->convertCollection($this->createQuery()->execute());
	}

	/**
	 * Returns the total number objects of this repository.
	 *
	 * @throws NoDatabaseSelectedException if no database has been selected
	 * @return integer The object count
	 * @api
	 */
	public function countAll() {
		$currentDatabase = $this->getDatabase();
		if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);

		$query = $this->createQuery();
		$query->matching($query->equals('db', $currentDatabase));
		return $query->execute()->count();
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @throws \Cundd\Rest\Domain\Exception\NoDatabaseSelectedException if no Document database has been selected
	 * @return void
	 *
	 * @api
	 */
	public function removeAll() {
		foreach ($this->findAll() AS $object) {
			$this->remove($object);
		}

		if ($this->useRawQueryResults) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);

			$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;
			$databaseConnection = $GLOBALS['TYPO3_DB'];

			$query = 'UPDATE tx_rest_domain_model_document SET deleted=2 WHERE db = \'' . $currentDatabase . '\'';
			$result = $databaseConnection->sql_query($query);
			var_dump($query);
			var_dump($result);
			var_dump($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
			return;

			$query = $this->createQuery();
			$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
			$query->statement('UPDATE `tx_rest_domain_model_document` SET `deleted`=2 WHERE db = ?', array($currentDatabase));
			// $query->statement('DELETE FROM tx_rest_domain_model_document WHERE db = ?', array($currentDatabase));
			$query->execute();
		}
	}


	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param integer $uid The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByUid($uid) {
		return $this->persistenceManager->getObjectByIdentifier($uid, $this->objectType);
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param mixed $identifier The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByIdentifier($identifier) {
		return $this->persistenceManager->getObjectByIdentifier($identifier, $this->objectType);
	}

//	/**
//	 * Sets the property names to order the result by per default.
//	 * Expected like this:
//	 * array(
//	 * 'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
//	 * 'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
//	 * )
//	 *
//	 * @param array $defaultOrderings The property names to order by
//	 * @return void
//	 * @api
//	 */
//	public function setDefaultOrderings(array $defaultOrderings) {
//		$this->defaultOrderings = $defaultOrderings;
//	}
//
//	/**
//	 * Sets the default query settings to be used in this repository
//	 *
//	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
//	 * @return void
//	 * @api
//	 */
//	public function setDefaultQuerySettings(\TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $defaultQuerySettings) {
//		$this->defaultQuerySettings = $defaultQuerySettings;
//	}

	/**
	 * Dispatches magic methods (findBy[Property]())
	 *
	 * @param string $methodName The name of the magic method
	 * @param string $arguments  The arguments of the magic method
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException
	 * @return mixed
	 * @api
	 */
	public function __call($methodName, $arguments) {
		// @todo: Fix me
//		$currentDatabase = $this->getDatabase();
//		if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);
//
//		$query = $this->createQuery();
//		$query->matching($query->equals('db', $currentDatabase));
//
//		if (substr($methodName, 0, 6) === 'findBy' && strlen($methodName) > 7) {
//			$propertyName = lcfirst(substr($methodName, 6));
//			$result = $query->matching($query->equals($propertyName, $arguments[0]))->execute();
//			return $result;
//		} elseif (substr($methodName, 0, 9) === 'findOneBy' && strlen($methodName) > 10) {
//			$propertyName = lcfirst(substr($methodName, 9));
//
//
//			$result = $query->matching($query->equals($propertyName, $arguments[0]))->setLimit(1)->execute();
//			if ($result instanceof \TYPO3\CMS\Extbase\Persistence\QueryResultInterface) {
//				return $result->getFirst();
//			} elseif (is_array($result)) {
//				return isset($result[0]) ? $result[0] : NULL;
//			}
//
//		} elseif (substr($methodName, 0, 7) === 'countBy' && strlen($methodName) > 8) {
//			$propertyName = lcfirst(substr($methodName, 7));
//			$result = $query->matching($query->equals($propertyName, $arguments[0]))->execute()->count();
//			return $result;
//		}
		throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException('The method "' . $methodName . '" is not supported by the repository.', 1233180480);
	}

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 * @api
	 */
	public function createQuery() {
		$query = $this->persistenceManager->createQueryForType($this->objectType);
		if ($this->defaultOrderings !== array()) {
			$query->setOrderings($this->defaultOrderings);
		}
		if ($this->defaultQuerySettings !== NULL) {
			$query->setQuerySettings(clone $this->defaultQuerySettings);
		}
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setReturnRawQueryResult($this->useRawQueryResults);
		return $query;
	}

	/**
	 * Search for Documents matching the given properties
	 *
	 * @param array   $properties Dictionary of property keys and values
	 * @param bool    $count      Return the number of matches
	 * @param integer $limit      Limit the number of matches
	 * @throws \Cundd\Rest\Domain\Exception\NoDatabaseSelectedException if the converted Document has no database
	 * @return mixed|null|object
	 */
	public function findWithProperties($properties, $count = FALSE, $limit = -1) {
		/** @var Query $query */
		$query = $this->createQuery();

		$constraintsCollection = array();

		if (isset($properties['guid'])) {
			list($database, $id) = explode('-', $properties['guid'], 2);
			$constraintsCollection[] = $query->equals('db', $database);
			$constraintsCollection[] = $query->equals('id', $id);
			unset($properties['guid']);
		} else {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) throw new NoDatabaseSelectedException('No Document database has been selected', 1389258204);
			$query->matching($query->equals('db', $currentDatabase));
		}

		$realDatabaseColumns = array('uid', 'pid', 'tstamp', 'crdate', 'cruser_id');
		foreach ($realDatabaseColumns as $realDatabaseColumn) {
			if (isset($properties[$realDatabaseColumn])) {
				$constraintsCollection[] = $query->equals($realDatabaseColumn, $properties[$realDatabaseColumn]);
				unset($properties[$realDatabaseColumn]);
			}
		}
		if (!empty($constraintsCollection)) {
			$query->matching($query->logicalAnd($constraintsCollection));
		}


		// Get the objects
		$resultCollection = $this->convertCollection($query->execute());

		// Filter using the remaining properties
		$filteredResultCollection = array();
		$filteredResultCollectionCount = 0;

		/** @var Document $currentDocument */

		/*
		 * Loop through each found Document and check each of the properties
		 * that were not filtered in the query
		 */
		foreach ($resultCollection as $currentDocument) {
			$isMatchingResult = TRUE;
			$currentDocumentData = $currentDocument->_getUnpackedData();

			foreach ($properties as $propertyKey => $propertyValue) {
				if ($propertyValue !== ObjectAccess::getPropertyPath($currentDocumentData, $propertyKey)) {
					$isMatchingResult = FALSE;
					break;
				}
			}

			if ($isMatchingResult) {
				$filteredResultCollection[] = $currentDocument;
				if (++$filteredResultCollectionCount > ($limit - 1)) {
					break;
				}
			}
		}
		return $filteredResultCollection;
	}

	/**
	 * Converts the query result into objects
	 *
	 * @param array|QueryResultInterface $resultCollection
	 * @return array<Document>
	 */
	protected function convertCollection($resultCollection) {
		if (!$this->useRawQueryResults) {
			if (is_object($resultCollection) && $resultCollection->count() === 0) {
				return array();
			}
			return $resultCollection;
		}
		$convertedObjects = array();
		foreach ($resultCollection as $resultSet) {
			$convertedObjects[] = $this->convertToDocument($resultSet);
		}

		return $convertedObjects;
	}

	/**
	 * Converts the query result set into objects
	 *
	 * @param array|\stdClass $input
	 * @throws \Cundd\Rest\Domain\Exception\NoDatabaseSelectedException if the converted Document has no database
	 * @return Document
	 */
	protected function convertToDocument($input) {
		if (!$input) {
			return NULL;
		}

		$inputData = $input;
		if (is_object($input)) {
			if ($input instanceof \stdClass) {
				$inputData = get_object_vars($input);
			}
		}

		$convertedObject = NULL;
		if (!$this->useRawQueryResults) {
			// If the input already is a Document return it
			if (is_object($input) && $input instanceof Document) {
				return $input;
			}

			switch (TRUE) {
				case isset($inputData['uid']):
					$convertedObject = $this->findByUid($inputData['uid']);
					break;

				case isset($inputData['id']) && $this->getDatabase():
					$convertedObject = $this->findByGuid($this->getDatabase() . '-' . $inputData['id']);
					break;

				case isset($inputData['id']):
					$convertedObject = $this->findById($inputData['id']);
					break;

				default:
			}
		}

		if (!$convertedObject) {
			$convertedObject = new Document();
		}

		/*
		 * Check if the input has a value for key 'data_protected' or
		 * 'dataProtected' and set it first
		 */
		if (isset($inputData['data_protected']) && $inputData['data_protected']) {
			$inputData[Document::DATA_PROPERTY_NAME] = $inputData['data_protected'];
			unset($inputData['data_protected']);
		}
		$key = Document::DATA_PROPERTY_NAME;
		if (isset($inputData[$key]) && $inputData[$key]) {
			$value = $inputData[$key];
			$convertedObject->setValueForKey($key, $value);
			unset($inputData[$key]);
		}

		/*
		 * Loop through each (remaining) key value pair from the input and
		 * assign it to the Document
		 */
		foreach ($inputData as $key => $value) {
//			if (ctype_lower($key[0])) { // Preserve the case
//				$key = GeneralUtility::underscoredToLowerCamelCase($key);
//			} else {
//				$key = GeneralUtility::underscoredToUpperCamelCase($key);
//			}
			$convertedObject->setValueForKey($key, $value);
		}

		/*
		 * Make sure the Document's database is set
		 */
		if (!$convertedObject->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$convertedObject->_setDb($currentDatabase);
		}
		return $convertedObject;
	}

	/**
	 * Merges two Documents
	 *
	 * @param Document                 $oldDocument
	 * @param Document|array|\stdClass $newDocument
	 * @throws \Cundd\Rest\Domain\Exception\NoDatabaseSelectedException if the converted Document has no database
	 * @return Document
	 */
	protected function mergeDocuments($oldDocument, $newDocument) {
		$mergeKeys = array('uid', 'pid', 'id', 'db', Document::DATA_PROPERTY_NAME);
		foreach ($mergeKeys as $key) {
			if (isset($newDocument[$key]) && $newDocument[$key]) {
				$oldDocument[$key] = $newDocument[$key];
			}
		}
		if (!is_object($oldDocument)) {
			Iresults::pd($oldDocument);
		}
		if (!$oldDocument->_getDb()) {
			$currentDatabase = $this->getDatabase();
			if (!$currentDatabase) {
				throw new NoDatabaseSelectedException('The given object and the repository have no database set', 1389257938);
			}
			$oldDocument->_setDb($currentDatabase);
		}
		return $oldDocument;
	}

	/**
	 * Invoked before a Document in the repository will be changed
	 *
	 * @param Document $document
	 */
	public function willChangeDocument($document) {
	}

	/**
	 * Invoked after a Document in the repository will be changed
	 *
	 * @param Document $document
	 */
	public function didChangeDocument($document) {
	}

	/**
	 * Returns the class name of this class.
	 *
	 * @return string Class name of the repository.
	 */
	protected function getRepositoryClassName() {
		return __CLASS__;
	}


}

?>