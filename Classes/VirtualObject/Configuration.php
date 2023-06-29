<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject;

/**
 * Virtual Object Configuration
 *
 * A Virtual Object Configuration is the definition of a REST resource without an associated Extbase Domain Model. This
 * allows the access to database records without the need to defined a Model class.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * The array that hold the configuration data
     *
     * @see "Resources/Private/Development/Virtual Object Configuration example.json" for the abstract schema
     * @var array
     */
    protected $configurationData = [];

    /**
     * A map of all the source keys and the associated property names
     *
     * @var array
     */
    protected $sourceKeyToPropertyMap = [];

    /**
     * Whether unknown (un-configured) properties should be skipped during mapping, or throw an exception
     *
     * @var boolean
     */
    protected $skipUnknownProperties = false;

    public function __construct($configurationData = [])
    {
        $this->configurationData = $configurationData;
    }

    public function getAllProperties(): array
    {
        return array_keys($this->configurationData['properties']);
    }

    public function getAllSourceKeys(): array
    {
        return array_map(
            function ($item) {
                return $item['column'];
            },
            array_values($this->configurationData['properties'])
        );
    }

    public function hasProperty(string $propertyName): bool
    {
        return isset($this->configurationData['properties'][$propertyName]);
    }

    public function hasSourceKey(string $sourceKey): bool
    {
        $sourceKeyToPropertyMap = $this->getSourceKeyToPropertyMap();

        return isset($sourceKeyToPropertyMap[$sourceKey]);
    }

    public function getConfigurationForProperty(string $propertyName): array
    {
        return isset($this->configurationData['properties'][$propertyName])
            ? $this->configurationData['properties'][$propertyName]
            : [];
    }

    /**
     * Returns the source property (column) name for the given property name, or NULL if it isn't defined
     *
     * @param string $propertyName
     * @return string
     */
    public function getSourceKeyForProperty(string $propertyName): ?string
    {
        if (!$this->hasProperty($propertyName)) {
            return null;
        }

        return isset($this->configurationData['properties'][$propertyName]['column'])
            ? $this->configurationData['properties'][$propertyName]['column']
            : null;
    }

    /**
     * Returns the property for the given source property (column)
     *
     * @param string $sourceKey
     * @return string
     */
    public function getPropertyForSourceKey(string $sourceKey): ?string
    {
        $sourceKeyToPropertyMap = $this->getSourceKeyToPropertyMap();

        return isset($sourceKeyToPropertyMap[$sourceKey])
            ? $sourceKeyToPropertyMap[$sourceKey]
            : null;
    }

    /**
     * Returns the data type for the given property name
     *
     * @param string $propertyName
     * @return string Returns one of the following: "string", "float", "int", "integer", "bool", "boolean"
     */
    public function getTypeForProperty(string $propertyName): ?string
    {
        if (!$this->hasProperty($propertyName)) {
            return null;
        }

        return isset($this->configurationData['properties'][$propertyName]['type'])
            ? $this->configurationData['properties'][$propertyName]['type']
            : null;
    }

    /**
     * Returns the source identifier (the database table name)
     *
     * @return string
     */
    public function getSourceIdentifier(): ?string
    {
        return isset($this->configurationData['tableName'])
            ? $this->configurationData['tableName']
            : null;
    }

    /**
     * Returns a map of all the source keys and the associated property names
     *
     * @return array
     */
    public function getSourceKeyToPropertyMap(): array
    {
        if (!$this->sourceKeyToPropertyMap) {
            foreach ($this->configurationData['properties'] as $propertyName => $propertyMapping) {
                $this->sourceKeyToPropertyMap[$propertyMapping['column']] = $propertyName;
            }
        }

        return $this->sourceKeyToPropertyMap;
    }

    /**
     * Set whether unknown (un-configured) properties should be skipped during mapping, or throw an exception
     *
     * @param boolean $skipUnknownProperties
     * @return $this
     */
    public function setSkipUnknownProperties(bool $skipUnknownProperties): ConfigurationInterface
    {
        $this->skipUnknownProperties = $skipUnknownProperties;

        return $this;
    }

    /**
     * Return whether unknown (un-configured) properties should be skipped during mapping, or throw an exception
     *
     * @return boolean
     */
    public function shouldSkipUnknownProperties(): bool
    {
        return $this->skipUnknownProperties;
    }

    /**
     * Returns the name of the property which uniquely identifies an object
     *
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return isset($this->configurationData['identifier']) ? $this->configurationData['identifier'] : null;
    }
}
