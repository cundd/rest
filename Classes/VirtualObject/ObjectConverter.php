<?php
declare(strict_types=1);

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

    public function __construct(ConfigurationInterface $configuration = null)
    {
        $this->configuration = $configuration;
    }

    /**
     * Converts the given Virtual Object's data into it's source representation
     *
     * @param array|null $virtualObjectData Raw data in the schema defined by the current mapping
     * @param bool       $replace           If TRUE the converted data will contain each property with a NULL value. If FALSE the result will only contain the keys defined in the source
     * @return array
     * @throws InvalidConverterTypeException
     * @throws InvalidPropertyException if a property is not defined in the mapping
     * @throws MissingConfigurationException if the configuration is not set
     */
    public function prepareDataFromVirtualObjectData(?array $virtualObjectData, bool $replace = true): array
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
     * @return array
     * @throws Exception\MissingConfigurationException if the configuration is not set
     * @throws InvalidPropertyException if a property is not defined in the mapping
     */
    public function convertFromVirtualObject($virtualObject): array
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
     * @return array
     * @throws MissingConfigurationException if the configuration is not set
     * @throws InvalidPropertyException if a property is not defined in the mapping
     */
    public function prepareForVirtualObjectData(array $source, bool $replace = true): array
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
     * @return VirtualObject
     * @throws MissingConfigurationException if the configuration is not set
     * @throws InvalidPropertyException if a property is not defined in the mapping
     */
    public function convertToVirtualObject(array $source): VirtualObject
    {
        return new VirtualObject($this->prepareForVirtualObjectData($source));
    }

    /**
     * Convert the given value to the specified type
     *
     * @param mixed  $value
     * @param string $type
     * @return mixed Returns the converted value
     * @throws Exception\InvalidConverterTypeException if the given type is not valid
     */
    public function convertToType($value, string $type)
    {
        $result = null;
        switch (strtolower($type)) {
            // Builtin types
            case 'integer':
            case 'int':
                return intval($value);

            case 'boolean':
            case 'bool':
                return (bool)$value;

            case 'float':
                return floatval($value);

            case 'string':
                return (string)$value;

            // Special types
            case 'slug':
                return (preg_match('/^[a-zA-Z0-9-_]+$/', (string)$value) > 0 ? (string)$value : null);

            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);

            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);

            case 'trim':
                return trim((string)$value);

            default:
                throw new InvalidConverterTypeException('Can not convert to type ' . $type, 1395661844);
        }
    }

    /**
     * Sets the configuration to use when converting
     *
     * @param ConfigurationInterface $configuration
     * @return self
     */
    public function setConfiguration(ConfigurationInterface $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Returns the configuration to use when converting
     *
     * @return ConfigurationInterface
     * @throws MissingConfigurationException if the configuration is not set
     */
    public function getConfiguration(): ConfigurationInterface
    {
        if (!$this->configuration) {
            throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
        }

        return $this->configuration;
    }
}
