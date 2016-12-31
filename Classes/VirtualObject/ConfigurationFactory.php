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

namespace Cundd\Rest\VirtualObject;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\SingletonInterface;

/**
 * The Configuration Factory allows the creation of Virtual Object Configurations from various sources
 */
class ConfigurationFactory implements SingletonInterface
{
    /**
     * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * ConfigurationFactory constructor.
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
    public function create()
    {
        return $this->createWithConfigurationData(array());
    }

    /**
     * Tries to read the configuration from the given array
     *
     * @param array        $configurationArray
     * @param ResourceType $resourceType
     * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
     */
    public function createFromArrayForResourceType($configurationArray, ResourceType $resourceType)
    {
        $resourceTypeString = (string)$resourceType;
        if (
            isset($configurationArray[$resourceTypeString]) && is_array($configurationArray[$resourceTypeString])
            && isset($configurationArray[$resourceTypeString]['mapping']) && is_array(
                $configurationArray[$resourceTypeString]['mapping']
            )
        ) {
            return $this->createWithConfigurationData($configurationArray[$resourceTypeString]['mapping']);
        }

        return null;
    }

    /**
     * Tries to read the configuration from TypoScript
     *
     * @param ResourceType $resourceType
     * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
     */
    public function createFromTypoScriptForResourceType(ResourceType $resourceType)
    {
        $configurationData = $this->configurationProvider->getSetting('virtualObjects.' . $resourceType);
        if (!isset($configurationData['mapping.'])) {
            return null;
        }
        $mapping = $configurationData['mapping.'];

        if (!isset($mapping['properties.'])) {
            return null;
        }

        $mergedConfigurationData = array(
            'identifier' => $mapping['identifier'],
            'tableName'  => $mapping['tableName'],
            'properties' => $mapping['properties.'],
        );

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
     * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
     */
    public function createFromJsonForResourceType($jsonString, ResourceType $resourceType)
    {
        $configurationData = json_decode($jsonString, true);
        if ($configurationData) {
            return $this->createFromArrayForResourceType($configurationData, $resourceType);
        }

        return null;
    }

    /**
     * Returns a new Configuration instance with the given data
     *
     * @param array $configurationData
     * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
     */
    public function createWithConfigurationData($configurationData)
    {
        $configurationObject = new Configuration(self::preparePropertyMapping($configurationData));

        if (isset($configurationData['skipUnknownProperties'])) {
            $configurationObject->setSkipUnknownProperties((bool)$configurationData['skipUnknownProperties']);
        }

        return $configurationObject;
    }

    /**
     * Prepares the given property mapping
     *
     * @param array $mapping
     * @return array
     */
    public static function preparePropertyMapping($mapping)
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

            $propertyMappingPrepared = array();
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

                $propertyMappingPrepared[$propertyKey] = array(
                    'type'   => $type,
                    'column' => $column,
                );
            }
            $mapping['properties'] = $propertyMappingPrepared;
        }

        return $mapping;
    }
}
