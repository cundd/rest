<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\VirtualObject;

/**
 * Interface for the Repository for Virtual Objects
 */
interface PersistenceManagerInterface
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
     * Returns the array of identifier properties of the object
     *
     * @param VirtualObject $object
     * @return array
     */
    public function getIdentifiersOfObject(VirtualObject $object): array;

    /**
     * Returns the array of identifier columns and value of the object
     *
     * @param VirtualObject $object
     * @return array
     */
    public function getIdentifierColumnsOfObject(VirtualObject $object): array;

    /**
     * Returns the source identifier (the database table name)
     *
     * @return string|null
     */
    public function getSourceIdentifier(): ?string;

    /**
     * Sets the configuration to use when converting
     *
     * @param ConfigurationInterface $configuration
     * @return self
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

    /**
     * Returns the number of items matching the query
     *
     * @param QueryInterface|array $query
     * @return integer
     * @api
     */
    public function getObjectCountByQuery(QueryInterface $query): int;

    /**
     * Returns the object data matching the $query
     *
     * @param QueryInterface|array $query
     * @return array
     * @api
     */
    public function getObjectDataByQuery(QueryInterface $query): array;

    /**
     * Returns the object with the given identifier
     *
     * @param string|int $identifier
     * @return VirtualObject|null
     */
    public function getObjectByIdentifier($identifier): ?VirtualObject;
}
