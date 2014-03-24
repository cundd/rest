<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 12:07
 */

namespace Cundd\Rest\VirtualObject;
use Cundd\Rest\VirtualObject\Exception\InvalidConverterTypeException;
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

	/**
	 * Converts the given Virtual Object into it's source representation
	 *
	 * @param VirtualObject $virtualObject
	 * @throws Exception\MissingConfigurationException if the configuration is not set
	 * @return array
	 */
	public function convertFromVirtualObject($virtualObject) {
		$convertedData = array();
		$configuration = $this->getConfiguration();

		if (!$configuration) {
			throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
		}

		foreach ($virtualObject->getData() as $propertyKey => $propertyValue) {
			if ($configuration->hasProperty($propertyKey)) {
				$type = $configuration->getTypeForProperty($propertyKey);
				$sourceKey = $configuration->getSourceKeyForProperty($propertyKey);
				$propertyValue = $this->convertToType($propertyValue, $type);

				$convertedData[$sourceKey] = $propertyValue;
			} else {
				#throw InvalidPropertyException
			}
		}
		return $convertedData;
	}

	/**
	 * Converts the given source array into a Virtual Object
	 *
	 * @param array $source
	 * @throws Exception\MissingConfigurationException if the configuration is not set
	 * @return VirtualObject
	 */
	public function convertToVirtualObject($source){
		$convertedData = array();
		$configuration = $this->getConfiguration();

		if (!$configuration) {
			throw new MissingConfigurationException('Virtual Object Configuration is not set', 1395666846);
		}

		foreach ($source as $sourceKey => $sourceValue) {
			if ($configuration->hasSourceKey($sourceKey)) {
				$propertyKey = $configuration->getPropertyForSourceKey($sourceKey);
				$type = $configuration->getTypeForProperty($propertyKey);
				$sourceValue = $this->convertToType($sourceValue, $type);

				$convertedData[$propertyKey] = $sourceValue;
			} else {
				#throw InvalidPropertyException
			}
		}
		return new VirtualObject($convertedData);
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
	 * @return \Cundd\Rest\VirtualObject\ConfigurationInterface
	 */
	public function getConfiguration() {
		return $this->configuration;
	}


}