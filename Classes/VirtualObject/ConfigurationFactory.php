<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\SingletonInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;

/**
 * The Configuration Factory allows the creation of Virtual Object Configurations from various sources
 */
class ConfigurationFactory implements SingletonInterface
{
    /**
     * @var ConfigurationProviderInterface
     */
    protected $configurationProvider;

    /**
     * Configuration Factory constructor
     *
     * @param ConfigurationProviderInterface $configurationProvider
     */
    public function __construct(ConfigurationProviderInterface $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * Returns a new "empty" Configuration instance
     *
     * @return ConfigurationInterface
     */
    public function create(): ConfigurationInterface
    {
        return $this->createWithConfigurationData([]);
    }

    /**
     * Tries to read the configuration from the given array
     *
     * @param array        $configurationArray
     * @param ResourceType $resourceType
     * @return ConfigurationInterface|null Returns the Configuration object or NULL if no matching configuration was found
     */
    public function createFromArrayForResourceType(
        array $configurationArray,
        ResourceType $resourceType
    ): ?ConfigurationInterface {
        $resourceTypeString = Utility::normalizeResourceType($resourceType);
        if (
            isset($configurationArray[$resourceTypeString])
            && is_array($configurationArray[$resourceTypeString])
            && isset($configurationArray[$resourceTypeString]['mapping'])
            && is_array($configurationArray[$resourceTypeString]['mapping'])
        ) {
            return $this->createWithConfigurationData($configurationArray[$resourceTypeString]['mapping']);
        }

        return null;
    }

    /**
     * Tries to read the configuration from TypoScript
     *
     * @param ResourceType $resourceType
     * @return ConfigurationInterface Returns the Configuration object
     * @throws MissingConfigurationException
     */
    public function createFromTypoScriptForResourceType(ResourceType $resourceType): ConfigurationInterface
    {
        $normalizedConfiguration = $this->normalizedVirtualObjectConfigurations(
            $this->configurationProvider->getSetting('virtualObjects')
        );

        $normalizeResourceType = Utility::normalizeResourceType($resourceType);

        if (!isset($normalizedConfiguration[$normalizeResourceType])) {
            throw new MissingConfigurationException(
                sprintf('Could not find configuration for Resource Type "%s"', (string)$resourceType)
            );
        }
        $configurationData = $normalizedConfiguration[$normalizeResourceType];

        if (!isset($configurationData['mapping.'])) {
            throw new MissingConfigurationException(
                sprintf('Key "mapping." not found in configuration for Resource Type "%s"', (string)$resourceType)
            );
        }
        $mapping = $configurationData['mapping.'];

        if (!isset($mapping['properties.'])) {
            throw new MissingConfigurationException(
                sprintf('Key "properties." not found in the mapping for Resource Type "%s"', (string)$resourceType)
            );
        }

        $mergedConfigurationData = [
            'identifier' => $mapping['identifier'],
            'tableName'  => $mapping['tableName'],
            'properties' => $mapping['properties.'],
        ];

        if (isset($mapping['skipUnknownProperties'])) {
            $mergedConfigurationData['skipUnknownProperties'] = $mapping['skipUnknownProperties'];
        }

        return $this->createWithConfigurationData($mergedConfigurationData);
    }

    /**
     * Tries to read the configuration from the given JSON string
     *
     * @param string $jsonString
     * @param        $resourceType
     * @return ConfigurationInterface|null Returns the Configuration object or NULL if no matching configuration was found
     */
    public function createFromJsonForResourceType(
        string $jsonString,
        ResourceType $resourceType
    ): ?ConfigurationInterface {
        $configurationData = json_decode($jsonString, true);
        if ($configurationData) {
            return $this->createFromArrayForResourceType(
                $this->normalizedVirtualObjectConfigurations($configurationData),
                $resourceType
            );
        }

        return null;
    }

    /**
     * Returns a new Configuration instance with the given data
     *
     * @param array $configurationData
     * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
     */
    public function createWithConfigurationData(array $configurationData): ConfigurationInterface
    {
        $configurationObject = new Configuration(self::preparePropertyMapping($configurationData));

        if (isset($configurationData['skipUnknownProperties'])) {
            $configurationObject->setSkipUnknownProperties((bool)$configurationData['skipUnknownProperties']);
        }

        return $configurationObject;
    }

    /**
     * Normalizes the Resource-Type-keys in the given configuration
     *
     * @param array $rawConfiguration
     * @return array
     */
    private function normalizedVirtualObjectConfigurations(array $rawConfiguration): array
    {
        $normalizedConfiguration = [];
        foreach ($rawConfiguration as $resourceType => $configuration) {
            $normalizedConfiguration[Utility::normalizeResourceType($resourceType)] = $configuration;
        }

        return $normalizedConfiguration;
    }

    /**
     * Prepares the given property mapping
     *
     * @param array $mapping
     * @return array
     */
    public static function preparePropertyMapping(array $mapping): array
    {
        /**
         * Remove the last character form the property key (used when imported from TypoScript)
         *
         * @var boolean $removeLastCharacter
         */
        $removeLastCharacter = -1;

        if (isset($mapping['properties']) || isset($mapping['properties.'])) {
            if (isset($mapping['properties.'])) {
                $propertyMapping = $mapping['properties.'];
                unset($mapping['properties.']);
            } else {
                $propertyMapping = $mapping['properties'];
            }

            $propertyMappingPrepared = [];
            foreach ($propertyMapping as $propertyKey => $propertyConfiguration) {
                // If the last character is a dot (".") remove the last character of all property keys
                if ($removeLastCharacter === -1) {
                    $removeLastCharacter = substr($propertyKey, -1) === '.';
                }

                if ($removeLastCharacter) {
                    $propertyKey = substr($propertyKey, 0, -1); // Strip the trailing "."
                }

                // If the current property configuration is a string, it defines the type
                if (is_string($propertyConfiguration)) {
                    $type = $propertyConfiguration;
                    $column = $propertyKey;
                } else {
                    // else it has to be an array
                    $type = $propertyConfiguration['type'];
                    $column = isset($propertyConfiguration['column']) ? $propertyConfiguration['column'] : $propertyKey;
                }

                $propertyMappingPrepared[$propertyKey] = [
                    'type'   => $type,
                    'column' => $column,
                ];
            }
            $mapping['properties'] = $propertyMappingPrepared;
        }

        return $mapping;
    }
}
