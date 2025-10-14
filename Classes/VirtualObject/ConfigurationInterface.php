<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject;

/**
 * Interface for the Virtual Object Configuration
 *
 * A Virtual Object Configuration is the definition of a REST resource without an associated Extbase Domain Model. This
 * allows the access to database records without the need to defined a Model class.
 */
interface ConfigurationInterface
{
    /**
     * Return the list of all properties
     */
    public function getAllProperties(): array;

    /**
     * Return the list of all source keys
     */
    public function getAllSourceKeys(): array;

    /**
     * Returns TRUE if the given property name exists
     */
    public function hasProperty(string $propertyName): bool;

    /**
     * Returns TRUE if the given source key is mapped
     *
     * Checks if one of the configured property mappings uses the given source key
     */
    public function hasSourceKey(string $sourceKey): bool;

    /**
     * Return the configuration for the given property name
     */
    public function getConfigurationForProperty(string $propertyName): array;

    /**
     * Return the source key (column name) for the given property name, or NULL if it isn't defined
     */
    public function getSourceKeyForProperty(string $propertyName): ?string;

    /**
     * Return the property for the given source property (column)
     */
    public function getPropertyForSourceKey(string $sourceKey): ?string;

    /**
     * Return the data type for the given property name
     *
     * @return string|null Returns one of the following simple "string", "float", "int", "integer", "bool", "boolean" or one of the complex types
     */
    public function getTypeForProperty(string $propertyName): ?string;

    /**
     * Return the source identifier (the database table name)
     */
    public function getSourceIdentifier(): ?string;

    /**
     * Return the name of the property which uniquely identifies an object
     */
    public function getIdentifier(): ?string;

    /**
     * Set whether unknown (un-configured) properties should be skipped during mapping, or throw an exception
     */
    public function setSkipUnknownProperties(bool $skipUnknownProperties): self;

    /**
     * Return whether unknown (un-configured) properties should be skipped during mapping, or throw an exception
     */
    public function shouldSkipUnknownProperties(): bool;
}
