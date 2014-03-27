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
use Cundd\Rest\VirtualObject\Exception\InvalidConverterTypeException;
use Cundd\Rest\VirtualObject\Exception\InvalidObjectException;
use Cundd\Rest\VirtualObject\Exception\InvalidPropertyException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;

/**
 * A converter responsible for transforming Virtual Objects from and to their source data
 *
 * @package Cundd\Rest\VirtualObject
 */
class ObjectConverter {
	/**
	 * The configuration to use when converting
	 *
	 * @var ConfigurationInterface
	 */
	protected $configuration;

	function __construct($configuration = array()) {
		$this->configuration = $configuration;
	}

	/**
	 * Converts the given Virtual Object's data into it's source representation
	 *
	 * @param array $virtualObjectData Raw data in the schema defined by the current mapping
	 * @throws InvalidPropertyException if a property is not defined in the mapping
	 * @throws Exception\MissingConfigurationException if the configuration is not set
	 * @return array
	 */
	public function prepareDataFromVirtualObjectData($virtualObjectData) {
		$convertedData = array();
		$configuration = $this->getConfiguration();

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
			} else if (!$configuration->shouldSkipUnknownProperties()) {
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
	public function convertFromVirtualObject($virtualObject) {
		$virtualObjectData = NULL;
		if (is_array($virtualObject)) {
			$virtualObjectData = $virtualObject;
		} else if ($virtualObject instanceof VirtualObject) {
			$virtualObjectData = $virtualObject->getData();
		}
		return $this->prepareDataFromVirtualObjectData($virtualObjectData);
	}

	/**
	 * Converts the given source array into the configured Virtual Object data
	 *
	 * @param array $source
	 * @throws InvalidPropertyException if a property is not defined in the mapping
	 * @throws MissingConfigurationException if the configuration is not set
	 * @return array
	 */
	public function prepareForVirtualObjectData($source){
		$convertedData = array();
		$configuration = $this->getConfiguration();

		if (!$configuration) {
			throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
		}

		foreach ($source as $sourceKey => $sourceValue) {
//			if ($sourceKey === '__identity') {
//				$sourceKey = $configuration->getSourceKeyForProperty($configuration->getIdentifier());
//				var_dump($sourceKey, $configuration->hasSourceKey($sourceKey));
//			}
			if ($configuration->hasSourceKey($sourceKey)) {
				$propertyKey = $configuration->getPropertyForSourceKey($sourceKey);
				$type = $configuration->getTypeForProperty($propertyKey);
				$sourceValue = $this->convertToType($sourceValue, $type);

				$convertedData[$propertyKey] = $sourceValue;
			} else if (!$configuration->shouldSkipUnknownProperties()) {
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
	public function convertToVirtualObject($source){
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
	protected function convertToType($value, $type) {
		$result = NULL;
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
				$result = (preg_match("/[a-zA-Z0-9-_]/", $value) > 0);
				break;

			case 'url':
				$result = filter_var($value, FILTER_SANITIZE_URL);
				break;

			case 'email':
				$result = filter_var($value, FILTER_SANITIZE_EMAIL);
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
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
		return $this;
	}

	/**
	 * Returns the configuration to use when converting
	 *
	 * @throws Exception\MissingConfigurationException if the configuration is not set
	 * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
	 */
	public function getConfiguration() {
		if (!$this->configuration) throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
		return $this->configuration;
	}


}
