<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 15:53
 */

namespace Cundd\Rest\VirtualObject\Persistence;
use Cundd\Rest\VirtualObject\VirtualObject;

/**
 * Interface for the Repository for Virtual Objects
 *
 * @package Cundd\Rest\VirtualObject\Persistence
 */
interface RepositoryInterface {
	/**
	 * Adds the given object to the database
	 *
	 * @param VirtualObject $object
	 * @return void
	 */
	public function add($object);

	/**
	 * Removes the given object from the database
	 *
	 * @param VirtualObject $object
	 * @return void
	 */
	public function remove($object);

	/**
	 * Updates the given object in the database
	 *
	 * @param VirtualObject $object
	 * @return void
	 */
	public function update($object);

	/**
	 * Returns all objects from the database
	 *
	 * @return array
	 */
	public function findAll();

	/**
	 * Returns the total number objects of this repository.
	 *
	 * @return integer The object count
	 * @api
	 */
	public function countAll();

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll();

	/**
	 * Returns the object with the given identifier
	 *
	 * @param string $identifier
	 * @return VirtualObject
	 */
	public function findByIdentifier($identifier);

	/**
	 * Returns the array of identifiers of the object
	 *
	 * @param object $object
	 * @return array
	 */
	public function getIdentifiersOfObject($object);

	/**
	 * Returns the source identifier (the database table name)
	 *
	 * @return string
	 */
	public function getSourceIdentifier();
}