<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 15:59
 */

namespace Cundd\Rest\VirtualObject\Persistence;


use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\VirtualObject;

/**
 * Repository for Virtual Objects
 *
 * @package Cundd\Rest\VirtualObject\Persistence
 */
class Repository implements RepositoryInterface {
	/**
	 * The configuration to use when converting
	 *
	 * @var ConfigurationInterface
	 */
	protected $configuration;

	/**
	 * @var \Cundd\Rest\VirtualObject\Persistence\BackendInterface
	 * @inject
	 */
	protected $backend;

	/**
	 * @var ObjectConverter
	 */
	protected $objectConverter;

	/**
	 * Adds the given object to the database
	 *
	 * @param VirtualObject $object
	 * @return void
	 */
	public function add($object) {
		$this->backend->addRow(
			$this->getSourceIdentifier(),
			$this->getObjectConverter()->convertFromVirtualObject($object)
		);
	}

	/**
	 * Removes the given object from the database
	 *
	 * @param VirtualObject $object
	 * @return void
	 */
	public function remove($object) {
		$this->backend->removeRow(
			$this->getSourceIdentifier(),
			$this->getObjectConverter()->convertFromVirtualObject($object)
		);
	}

	/**
	 * Updates the given object in the database
	 *
	 * @param VirtualObject $object
	 * @return void
	 */
	public function update($object) {
		$identifierQuery = $this->getIdentifiersOfObject($object);
		if (
			$identifierQuery
			&& $this->backend->getObjectCountByQuery($this->getSourceIdentifier(), $identifierQuery)
		) {
			$this->backend->updateRow(
				$this->getSourceIdentifier(),
				$identifierQuery,
				$this->getObjectConverter()->convertFromVirtualObject($object)
			);
		}
	}

	/**
	 * Returns all objects from the database
	 *
	 * @return array
	 */
	public function findAll() {
		$objectConverter = $this->getObjectConverter();
		$objectCollection = array();
		$rawObjectCollection = $this->backend->getObjectDataByQuery($this->getSourceIdentifier(), array());
		foreach ($rawObjectCollection as $rawObjectData) {
			$objectCollection[] = $objectConverter->convertToVirtualObject($rawObjectData);
		}
		return $objectCollection;
	}

	/**
	 * Returns the total number objects of this repository.
	 *
	 * @return integer The object count
	 * @api
	 */
	public function countAll() {
		return $this->backend->getObjectCountByQuery($this->getSourceIdentifier(), array());
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll() {
		foreach ($this->findAll() as $object) {
			$this->remove($object);
		}
	}

	/**
	 * Returns the object with the given identifier
	 *
	 * @param string $identifier
	 * @return VirtualObject
	 */
	public function findByIdentifier($identifier) {
		$identifierKey = $this->getConfiguration()->getIdentifier();


		$objectConverter = $this->getObjectConverter();
		$objectCollection = array();

		$query = array(
			$identifierKey => $identifier
		);
		$rawObjectCollection = $this->backend->getObjectDataByQuery($this->getSourceIdentifier(), $query);
		foreach ($rawObjectCollection as $rawObjectData) {
			return $objectConverter->convertToVirtualObject($rawObjectData);
		}
		return NULL;
	}

	/**
	 * Returns the array of identifiers of the object
	 *
	 * @param object $object
	 * @return array
	 */
	public function getIdentifiersOfObject($object) {
		$objectData = $object->getData();
		$identifier = $this->getConfiguration()->getIdentifier();
		return isset($objectData[$identifier]) ? array($identifier => $objectData[$identifier]) : array();
	}


	/**
	 * Returns the source identifier (the database table name)
	 *
	 * @return string
	 */
	public function getSourceIdentifier() {
		return $this->getConfiguration()->getSourceIdentifier();
	}

	/**
	 * Sets the configuration to use when converting
	 *
	 * @param \Cundd\Rest\VirtualObject\ConfigurationInterface $configuration
	 * @return $this
	 */
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
		$this->objectConverter = NULL;
		return $this;
	}

	/**
	 * Returns the configuration to use when converting
	 *
	 * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException if the configuration is not set
	 * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
	 */
	public function getConfiguration() {
		if (!$this->configuration) {
			throw new MissingConfigurationException('Configuration not set', 1395681118);
		}
		return $this->configuration;
	}

	/**
	 * Returns the Object Converter for the current configuration
	 *
	 * @return ObjectConverter
	 */
	protected function getObjectConverter() {
		if (!$this->objectConverter) {
			$this->objectConverter = new ObjectConverter($this->getConfiguration());
		}
		return $this->objectConverter;
	}




} 