<?php

namespace Cundd\Rest\VirtualObject;

use Cundd\Rest\VirtualObject\Exception\InvalidConverterTypeException;
use Cundd\Rest\VirtualObject\Exception\InvalidPropertyException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;

/**
 * A converter responsible for transforming Virtual Objects from and to their source data
 */
class ObjectConverter
{
    /**
     * The configuration to use when converting
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    public function __construct($configuration = null)
    {
        $this->configuration = $configuration;
    }

    /**
     * Converts the given Virtual Object's data into it's source representation
     *
     * @param array $virtualObjectData Raw data in the schema defined by the current mapping
     * @param bool  $replace           If TRUE the converted data will contain each property with a NULL value. If FALSE the result will only contain the keys defined in the source
     * @throws InvalidPropertyException if a property is not defined in the mapping
     * @throws Exception\MissingConfigurationException if the configuration is not set
     * @return array
     */
    public function prepareDataFromVirtualObjectData($virtualObjectData, $replace = true)
    {
        $configuration = $this->getConfiguration();
        $convertedData = [];
        if ($replace) {
            $convertedData = array_fill_keys($configuration->getAllSourceKeys(), null);
        }

        if (!$configuration) {
            throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
        }

        foreach ($virtualObjectData as $propertyKey => $propertyValue) {
            if ($propertyKey === '__identity') {
                $propertyKey = $configuration->getIdentifier();
            }

            if ($configuration->hasProperty($propertyKey)) {
                $type = $configuration->getTypeForProperty($propertyKey);
                $sourceKey = $configuration->getSourceKeyForProperty($propertyKey);
                $propertyValue = $this->convertToType($propertyValue, $type);

                $convertedData[$sourceKey] = $propertyValue;
            } elseif (!$configuration->shouldSkipUnknownProperties()) {
                throw new InvalidPropertyException('Property "' . $propertyKey . '" is not defined', 1395670264);
            }
        }

        return $convertedData;
    }

    /**
     * Converts the given Virtual Object into it's source representation
     *
     * @param VirtualObject|array $virtualObject Either a Virtual Object instance or raw data in the schema defined by the current mapping
     * @throws InvalidPropertyException if a property is not defined in the mapping
     * @throws Exception\MissingConfigurationException if the configuration is not set
     * @return array
     */
    public function convertFromVirtualObject($virtualObject)
    {
        $virtualObjectData = null;
        if (is_array($virtualObject)) {
            $virtualObjectData = $virtualObject;
        } elseif ($virtualObject instanceof VirtualObject) {
            $virtualObjectData = $virtualObject->getData();
        }

        return $this->prepareDataFromVirtualObjectData($virtualObjectData);
    }

    /**
     * Converts the given source array into the configured Virtual Object data
     *
     * @param array $source
     * @param bool  $replace If TRUE the converted data will contain each property with a NULL value. If FALSE the result will only contain the keys defined in the source
     * @throws InvalidPropertyException if a property is not defined in the mapping
     * @throws MissingConfigurationException if the configuration is not set
     * @return array
     */
    public function prepareForVirtualObjectData($source, $replace = true)
    {
        $configuration = $this->getConfiguration();
        $convertedData = [];
        if ($replace) {
            $convertedData = array_fill_keys($configuration->getAllProperties(), null);
        }

        if (!$configuration) {
            throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
        }

        foreach ($source as $sourceKey => $sourceValue) {
            if ($configuration->hasSourceKey($sourceKey)) {
                $propertyKey = $configuration->getPropertyForSourceKey($sourceKey);
                $type = $configuration->getTypeForProperty($propertyKey);
                $sourceValue = $this->convertToType($sourceValue, $type);

                $convertedData[$propertyKey] = $sourceValue;
            } elseif (!$configuration->shouldSkipUnknownProperties()) {
                throw new InvalidPropertyException('Property "' . $sourceKey . '" is not defined', 1395670264);
            }
        }

        return $convertedData;
    }

    /**
     * Converts the given source array into a Virtual Object
     *
     * @param array $source
     * @throws InvalidPropertyException if a property is not defined in the mapping
     * @throws MissingConfigurationException if the configuration is not set
     * @return VirtualObject
     */
    public function convertToVirtualObject($source)
    {
        return new VirtualObject($this->prepareForVirtualObjectData($source));
    }

    /**
     * Convert the given value to the specified type
     *
     * @param mixed  $value
     * @param string $type
     * @throws Exception\InvalidConverterTypeException if the given type is not valid
     * @return mixed Returns the converted value
     */
    public function convertToType($value, $type)
    {
        $result = null;
        switch (strtolower($type)) {
            // Builtin types
            case 'integer':
            case 'int':
                $result = intval($value);
                break;

            case 'boolean':
            case 'bool':
                $result = (bool)$value;
                break;

            case 'float':
                $result = floatval($value);
                break;

            case 'string':
                $result = (string)$value;
                break;


            // Special types
            case 'slug':
                $result = (preg_match('/^[a-zA-Z0-9-_]+$/', $value) > 0 ? (string)$value : null);
                break;

            case 'url':
                $result = filter_var($value, FILTER_SANITIZE_URL);
                break;

            case 'email':
                $result = filter_var($value, FILTER_SANITIZE_EMAIL);
                break;

            case 'trim':
                $result = trim($value);
                break;


            default:
                throw new InvalidConverterTypeException('Can not convert to type ' . $type, 1395661844);
        }

        return $result;
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

        return $this;
    }

    /**
     * Returns the configuration to use when converting
     *
     * @throws Exception\MissingConfigurationException if the configuration is not set
     * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
     */
    public function getConfiguration()
    {
        if (!$this->configuration) {
            throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
        }

        return $this->configuration;
    }
}
