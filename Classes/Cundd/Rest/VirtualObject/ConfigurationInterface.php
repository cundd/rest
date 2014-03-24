<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 10:38
 */

namespace Cundd\Rest\VirtualObject;

/**
 * Interface for the Virtual Object Configuration
 *
 * A Virtual Object Configuration is the definition of a REST resource without an associated Extbase Domain Model. This
 * allows the access to database records without the need to defined a Model class.
 *
 *
 * @package Cundd\Rest\VirtualObject
 */
interface ConfigurationInterface {
	/**
	 * Returns TRUE if the given property name should be mapped, FALSE otherwise.
	 *
	 * @param string $propertyName
	 * @return boolean
	 */
	public function hasProperty($propertyName);

	/**
	 * Returns the configuration for the given property name
	 *
	 * @param string $propertyName
	 * @return array
	 */
	public function getConfigurationForProperty($propertyName);

	/**
	 * Returns the source property (column) name for the given property name
	 *
	 * @param string $propertyName
	 * @return string
	 */
	public function getSourcePropertyNameForProperty($propertyName);

	/**
	 * Returns the data type for the given property name
	 *
	 * @param string $propertyName
	 * @return string Returns one of the following: "string", "float", "int", "integer", "bool", "boolean"
	 */
	public function getTypeForProperty($propertyName);

	/**
	 * Returns the source identifier (the database table name)
	 *
	 * @return string
	 */
	public function getSourceIdentifier();


} 