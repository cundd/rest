<?php

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
     * Returns the list of all properties
     *
     * @return array
     */
    public function getAllProperties();

    /**
     * Returns the list of all source keys
     *
     * @return array
     */
    public function getAllSourceKeys();

    /**
     * Returns TRUE if the given property name exists
     *
     * @param string $propertyName
     * @return boolean
     */
    public function hasProperty($propertyName);

    /**
     * Returns TRUE if the given source key is mapped
     *
     * Checks if one of the configured property mappings uses the given source key
     *
     * @param string $sourceKey
     * @return boolean
     */
    public function hasSourceKey($sourceKey);

    /**
     * Returns the configuration for the given property name
     *
     * @param string $propertyName
     * @return array
     */
    public function getConfigurationForProperty($propertyName);

    /**
     * Returns the source key (column name) for the given property name
     *
     * @param string $propertyName
     * @return string
     */
    public function getSourceKeyForProperty($propertyName);

    /**
     * Returns the property for the given source property (column)
     *
     * @param $sourceKey
     * @return string
     */
    public function getPropertyForSourceKey($sourceKey);

    /**
     * Returns the data type for the given property name
     *
     * @param string $propertyName
     * @return string Returns one of the following simple "string", "float", "int", "integer", "bool", "boolean" or one of the complex types
     */
    public function getTypeForProperty($propertyName);

    /**
     * Returns the source identifier (the database table name)
     *
     * @return string
     */
    public function getSourceIdentifier();

    /**
     * Returns the name of the property which uniquely identifies an object
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set whether unknown (un-configured) properties should be skipped during mapping, or throw an exception
     *
     * @param boolean $skipUnknownProperties
     * @return $this
     */
    public function setSkipUnknownProperties($skipUnknownProperties);

    /**
     * Return whether unknown (un-configured) properties should be skipped during mapping, or throw an exception
     *
     * @return boolean
     */
    public function shouldSkipUnknownProperties();
}
