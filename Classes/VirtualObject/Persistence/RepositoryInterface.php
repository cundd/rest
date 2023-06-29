<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
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
    public function add(VirtualObject $object): void;

    /**
     * Removes the given object from the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function remove(VirtualObject $object): void;

    /**
     * Updates the given object in the database
     *
     * @param VirtualObject $object
     * @return void
     */
    public function update(VirtualObject $object): void;

    /**
     * Returns all objects from the database
     *
     * @return array
     */
    public function findAll(): iterable;

    /**
     * Returns the total number objects of this repository.
     *
     * @return integer The object count
     * @api
     */
    public function countAll(): int;

    /**
     * Removes all objects of this repository as if remove() was called for
     * all of them.
     *
     * @return void
     * @api
     */
    public function removeAll(): void;

    /**
     * Returns the object with the given identifier
     *
     * @param string|int $identifier
     * @return VirtualObject|null
     */
    public function findByIdentifier($identifier): ?VirtualObject;

    /**
     * Sets the configuration to use when converting
     *
     * @param ConfigurationInterface $configuration
     * @return $this
     */
    public function setConfiguration(ConfigurationInterface $configuration): self;

    /**
     * Returns the configuration to use when converting
     *
     * @return ConfigurationInterface
     * @throws MissingConfigurationException if the configuration is not set
     */
    public function getConfiguration(): ConfigurationInterface;

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
    public function registerObject(VirtualObject $object): VirtualObject;
}
