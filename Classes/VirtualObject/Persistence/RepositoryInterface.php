<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\VirtualObject;

/**
 * Interface for the Repository for Virtual Objects
 */
interface RepositoryInterface
{
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
     * Returns all objects from the database
     *
     * @return array
     */
    public function findAll();

    /**
     * Returns the total number objects of this repository.
     *
     * @return integer The object count
     * @api
     */
    public function countAll();

    /**
     * Removes all objects of this repository as if remove() was called for
     * all of them.
     *
     * @return void
     * @api
     */
    public function removeAll();

    /**
     * Returns the object with the given identifier
     *
     * @param string $identifier
     * @return VirtualObject
     */
    public function findByIdentifier($identifier);

    /**
     * Sets the configuration to use when converting
     *
     * @param \Cundd\Rest\VirtualObject\ConfigurationInterface $configuration
     * @return $this
     */
    public function setConfiguration(ConfigurationInterface $configuration);

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
}
