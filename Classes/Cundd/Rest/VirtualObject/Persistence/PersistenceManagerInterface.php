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

use Cundd\Rest\VirtualObject\VirtualObject;

/**
 * Interface for the Repository for Virtual Objects
 *
 * @package Cundd\Rest\VirtualObject\Persistence
 */
interface PersistenceManagerInterface {
    /**
     * Adds the given object to the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function add($object);

    /**
     * Removes the given object from the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function remove($object);

    /**
     * Updates the given object in the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function update($object);

    /**
     * Returns the array of identifier properties of the object
     *
     * @param object $object
     * @return array
     */
    public function getIdentifiersOfObject($object);

    /**
     * Returns the array of identifier columns and value of the object
     *
     * @param object $object
     * @return array
     */
    public function getIdentifierColumnsOfObject($object);

    /**
     * Returns the source identifier (the database table name)
     *
     * @return string
     */
    public function getSourceIdentifier();

    /**
     * Sets the configuration to use when converting
     *
     * @param \Cundd\Rest\VirtualObject\ConfigurationInterface $configuration
     * @return $this
     */
    public function setConfiguration($configuration);

    /**
     * Returns the configuration to use when converting
     *
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException if the configuration is not set
     * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
     */
    public function getConfiguration();

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
    public function registerObject($object);

    /**
     * Returns the number of items matching the query
     *
     * @param QueryInterface|array $query
     * @return integer
     * @api
     */
    public function getObjectCountByQuery($query);

    /**
     * Returns the object data matching the $query
     *
     * @param QueryInterface|array $query
     * @return array
     * @api
     */
    public function getObjectDataByQuery($query);

    /**
     * Returns the object with the given identifier
     *
     * @param string $identifier
     * @return VirtualObject
     */
    public function getObjectByIdentifier($identifier);
}
