<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 25.03.14
 * Time: 14:37
 */

namespace Cundd\Rest;

/**
 * Interface for handlers of API requests
 *
 * @package Cundd\Rest
 */
interface HandlerInterface {
	/**
	 * Sets the current request
	 *
	 * @param \Cundd\Rest\Request $request
	 * @return $this
	 */
	public function setRequest($request);

	/**
	 * Returns the current request
	 *
	 * @return \Cundd\Rest\Request
	 */
	public function getRequest();

	/**
	 * Returns the unique identifier of the currently matching Domain Model
	 *
	 * @return string
	 */
	public function getIdentifier();

	/**
	 * Sets the unique identifier of the currently matching Domain Model
	 *
	 * @param string $identifier
	 * @return $this
	 */
	public function setIdentifier($identifier);

	/**
	 * Returns the given property of the currently matching Model
	 *
	 * @param string $propertyKey
	 * @return mixed
	 */
	public function getProperty($propertyKey);

	/**
	 * Returns the data of the current Model
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function show();

	/**
	 * Replaces the currently matching Model with the data from the request
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function replace();

	/**
	 * Updates the currently matching Model with the data from the request
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function update();

	/**
	 * Deletes the currently matching Model
	 *
	 * @return integer Returns 200 an success
	 */
	public function delete();

	/**
	 * Creates a new Model with the data from the request
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function create();

	/**
	 * List all Models
	 *
	 * @return array Returns all Models
	 */
	public function listAll();

	/**
	 * Configures which method is responsible for handling the different request paths
	 */
	public function configureApiPaths();

} 