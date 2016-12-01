<?php
/*
 *  Copyright notice
 *
 *  (c) 2016 Daniel Corn <info@cundd.net>, cundd
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

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27.03.14
 * Time: 17:16
 */

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\VirtualObject;

class PersistenceManager implements PersistenceManagerInterface
{
    /**
     * @var \Cundd\Rest\ObjectManager
     * @inject
     */
    protected $objectManager;

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
     * The configuration to use when converting
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * Registers the given Virtual Object
     *
     * This is a high level shorthand for:
     * Object exists?
     *    Yes -> update
     *    No -> add
     *
     * @param VirtualObject $object
     * @return VirtualObject Returns the registered Document
     */
    public function registerObject($object)
    {
        $identifierQuery = $this->getIdentifierColumnsOfObject($object);
        if (
            $identifierQuery
            && $this->backend->getObjectCountByQuery($this->getSourceIdentifier(), $identifierQuery)
        ) {
            $this->update($object);
        } else {
            $this->add($object);
        }
        return $object;
    }

    /**
     * Adds the given object to the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function add($object)
    {
        $identifierValue = $this->backend->addRow(
            $this->getSourceIdentifier(),
            $this->getObjectConverter()->convertFromVirtualObject($object)
        );
        $identifierKey = $this->getConfiguration()->getIdentifier();
        $object->setValueForKey($identifierKey, $identifierValue);
    }

    /**
     * Updates the given object in the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function update($object)
    {
        $identifierQuery = $this->getIdentifierColumnsOfObject($object);
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
     * Removes the given object from the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function remove($object)
    {
        $identifierQuery = $this->getIdentifierColumnsOfObject($object);
        if (
            $identifierQuery
            && $this->backend->getObjectCountByQuery($this->getSourceIdentifier(), $identifierQuery)
        ) {
            $this->backend->removeRow(
                $this->getSourceIdentifier(),
                $identifierQuery
            );
        }
    }

    /**
     * Returns the number of items matching the query
     *
     * @param QueryInterface|array $query
     * @return integer
     * @api
     */
    public function getObjectCountByQuery($query)
    {
        return $this->backend->getObjectCountByQuery($this->getSourceIdentifier(), $query);
    }

    /**
     * Returns the object data matching the $query
     *
     * @param QueryInterface|array $query
     * @return array
     * @api
     */
    public function getObjectDataByQuery($query)
    {
        $objectConverter = $this->getObjectConverter();
        $objectCollection = array();

        $rawObjectCollection = $this->backend->getObjectDataByQuery($this->getSourceIdentifier(), $query);
        foreach ($rawObjectCollection as $rawObjectData) {
            $objectCollection[] = $objectConverter->convertToVirtualObject($rawObjectData);
        }
        return $objectCollection;
    }

    /**
     * Returns the object with the given identifier
     *
     * @param string $identifier
     * @return VirtualObject
     */
    public function getObjectByIdentifier($identifier)
    {
        $configuration = $this->getConfiguration();

        $identifierProperty = $configuration->getIdentifier();
        $identifierKey = $configuration->getSourceKeyForProperty($identifierProperty);


        $objectConverter = $this->getObjectConverter();
        $query = array(
            $identifierKey => $identifier
        );

        $rawObjectCollection = $this->backend->getObjectDataByQuery($this->getSourceIdentifier(), $query);
        foreach ($rawObjectCollection as $rawObjectData) {
            return $objectConverter->convertToVirtualObject($rawObjectData);
        }
        return null;
    }

    /**
     * Returns the array of identifier properties of the object
     *
     * @param object $object
     * @return array
     */
    public function getIdentifiersOfObject($object)
    {
        $objectData = $object->getData();
        $identifier = $this->getConfiguration()->getIdentifier();
        return isset($objectData[$identifier]) ? array($identifier => $objectData[$identifier]) : array();
    }

    /**
     * Returns the array of identifier columns and value of the object
     *
     * @param object $object
     * @return array
     */
    public function getIdentifierColumnsOfObject($object)
    {
        $configuration = $this->getConfiguration();
        $objectData = $object->getData();
        $identifier = $configuration->getIdentifier();
        $identifierColumn = $configuration->getSourceKeyForProperty($identifier);
        return isset($objectData[$identifier]) ? array($identifierColumn => $objectData[$identifier]) : array();
    }


    /**
     * Returns the source identifier (the database table name)
     *
     * @return string
     */
    public function getSourceIdentifier()
    {
        return $this->getConfiguration()->getSourceIdentifier();
    }

    /**
     * Sets the configuration to use when converting
     *
     * @param \Cundd\Rest\VirtualObject\ConfigurationInterface $configuration
     * @return $this
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        $this->objectConverter = null;
        return $this;
    }

    /**
     * Returns the configuration to use when converting
     *
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException if the configuration is not set
     * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
     */
    public function getConfiguration()
    {
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
    protected function getObjectConverter()
    {
        if (!$this->objectConverter) {
            $this->objectConverter = new ObjectConverter($this->getConfiguration());
        }
        return $this->objectConverter;
    }
}
