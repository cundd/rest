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
use TYPO3\CMS\Core\SingletonInterface;

/**
 * The Configuration Factory allows the creation of Virtual Object Configurations from various sources
 *
 * @package Cundd\Rest\VirtualObject
 */
class ConfigurationFactory implements SingletonInterface {
	/**
	 * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
	 */
	protected $configurationProvider;

	/**
	 * @param \Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider
	 */
	public function injectConfigurationProvider(\Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider) {
		$this->configurationProvider = $configurationProvider;
	}

	/**
	 * Returns a new "empty" Configuration instance
	 *
	 * @return ConfigurationInterface
	 */
	public function create() {
		return $this->_createWithConfigurationData(array());
	}

	/**
	 * Tries to read the configuration from the given array
	 *
	 * @param array $configurationArray
	 * @param       $path
	 * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
	 */
	public function createFromArrayForPath($configurationArray, $path) {
		$configurationData = NULL;
		if (
			isset($configurationArray[$path]) && is_array($configurationArray[$path])
			&& isset($configurationArray[$path]['mapping']) && is_array($configurationArray[$path]['mapping'])) {
			return $this->_createWithConfigurationData($configurationArray[$path]['mapping']);
		}
		return NULL;
	}

	/**
	 * Tries to read the configuration from TypoScript
	 *
	 * @param string $path
	 * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
	 */
	public function createFromTypoScriptForPath($path) {
		$configurationData = $this->configurationProvider->getSetting('virtualObjects.' . $path);
		if (!isset($configurationData['mapping.'])) {
			return NULL;
		}
		$mapping = $configurationData['mapping.'];

		if (!isset($mapping['properties.'])) {
			return NULL;
		}

		// Parse the TypoScript array
		$propertyMapping = array();
		$propertyMappingRaw = $mapping['properties.'];
		foreach ($propertyMappingRaw as $propertyKey => $propertyConfiguration) {
			$propertyKey = substr($propertyKey, 0, -1); // Strip the trailing "."
			$propertyMapping[$propertyKey] = array(
				'type' => $propertyConfiguration['type'],
				'column' => isset($propertyConfiguration['column']) ? $propertyConfiguration['column'] : $propertyKey,
			);
		}

		$mergedConfigurationData = array(
			'identifier' => $mapping['identifier'],
			'tableName' => $mapping['tableName'],
			'properties' => $propertyMapping
		);

		if (isset($mapping['skipUnknownProperties'])) {
			$mergedConfigurationData['skipUnknownProperties'] = $mapping['skipUnknownProperties'];
		}
		return $this->_createWithConfigurationData($mergedConfigurationData);
	}

	/**
	 * Tries to read the configuration from the given JSON string
	 *
	 * @param string $jsonString
	 * @param        $path
	 * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
	 */
	public function createFromJsonForPath($jsonString, $path) {
		$configurationData = json_decode($jsonString, TRUE);
		if ($configurationData) {
			return $this->createFromArrayForPath($configurationData, $path);
		}
		return NULL;
	}

	/**
	 * Tries to read the configuration from the given array
	 *
	 * @param array $configurationData
	 * @return ConfigurationInterface Returns the Configuration object or NULL if no matching configuration was found
	 */
	protected function _createWithConfigurationData($configurationData) {
		$configurationObject = new Configuration($configurationData);

		if (isset($configurationData['skipUnknownProperties'])) {
			$configurationObject->setSkipUnknownProperties((bool)$configurationData['skipUnknownProperties']);
		}
		return $configurationObject;
	}
}
