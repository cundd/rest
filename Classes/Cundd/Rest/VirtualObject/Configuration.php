<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 10:43
 */

namespace Cundd\Rest\VirtualObject;

/**
 * Virtual Object Configuration
 *
 * A Virtual Object Configuration is the definition of a REST resource without an associated Extbase Domain Model. This
 * allows the access to database records without the need to defined a Model class.
 *
 * @package Cundd\Rest\VirtualObject
 */
class Configuration implements ConfigurationInterface {
	/**
	 * The array that hold the configuration data
	 *
	 * @see "Resources/Private/Development/Virtual Object Configuration example.json" for the abstract schema
	 * @var array
	 */
	protected $configurationData = array();

	function __construct($configurationData = array()) {
		$this->configurationData = $configurationData;
	}

	/**
	 * Returns TRUE if the given property name should be mapped, FALSE otherwise.
	 *
	 * @param string $propertyName
	 * @return boolean
	 */
	public function hasProperty($propertyName) {
		return isset($this->configurationData['properties'][$propertyName]);
	}

	/**
	 * Returns the configuration for the given property name
	 *
	 * @param string $propertyName
	 * @return array
	 */
	public function getConfigurationForProperty($propertyName) {
		return isset($this->configurationData['properties'][$propertyName])
			? $this->configurationData['properties'][$propertyName]
			: array();
	}

	/**
	 * Returns the source property (column) name for the given property name, or NULL if it isn't defined
	 *
	 * @param string $propertyName
	 * @return string
	 */
	public function getSourcePropertyNameForProperty($propertyName) {
		if (!$this->hasProperty($propertyName)) {
			return NULL;
		}
		return isset($this->configurationData['properties'][$propertyName]['column'])
			? $this->configurationData['properties'][$propertyName]['column']
			: NULL;
	}

	/**
	 * Returns the data type for the given property name
	 *
	 * @param string $propertyName
	 * @return string Returns one of the following: "string", "float", "int", "integer", "bool", "boolean"
	 */
	public function getTypeForProperty($propertyName) {
		if (!$this->hasProperty($propertyName)) {
			return NULL;
		}
		return isset($this->configurationData['properties'][$propertyName]['type'])
			? $this->configurationData['properties'][$propertyName]['type']
			: NULL;
	}

	/**
	 * Returns the source identifier (the database table name)
	 *
	 * @return string
	 */
	public function getSourceIdentifier() {
		return isset($this->configurationData['tableName'])
			? $this->configurationData['tableName']
			: NULL;
	}


} 