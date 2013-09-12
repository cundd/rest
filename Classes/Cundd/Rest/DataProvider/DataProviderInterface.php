<?php
namespace Cundd\Rest\DataProvider;


use TYPO3\CMS\Core\SingletonInterface;

interface DataProviderInterface extends SingletonInterface {
	/**
	 * Returns the domain model repository for the models the given API path points to
	 *
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
	 */
	public function getRepositoryForPath($path);

	/**
	 * Returns the domain model repository class name for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return string
	 */
	public function getRepositoryClassForPath($path);

	/**
	 * Returns a domain model for the given API path and data
	 * This method will load existing models.
	 *
	 * @param array|string|int $data Data of the new model or it's UID
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithDataForPath($data, $path);

	/**
	 * Returns a domain model for the given API path and data
	 * Even if the data contains an identifier, the existing model will not be loaded.
	 *
	 * @param array|string|int $data Data of the new model or it's UID
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getNewModelWithDataForPath($data, $path);

	/**
	 * Returns the domain model class name for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return string
	 */
	public function getModelClassForPath($path);

	/**
	 * Returns the data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 */
	public function getModelData($model);

	/**
	 * Returns the property data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $propertyKey
	 * @return mixed
	 */
	public function getModelProperty($model, $propertyKey);

	/**
	 * Adds or updates the given model in the repository for the
	 * given API path
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $path The API path
	 * @return void
	 */
	public function saveModelForPath($model, $path);

	/**
	 * Tells the Data Provider to replace the given old model with the new one
	 * in the repository for the given API path
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $oldModel
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $newModel
	 * @param string $path The API path
	 * @return void
	 */
	public function replaceModelForPath($oldModel, $newModel, $path);

	/**
	 * Adds or updates the given model in the repository for the
	 * given API path
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $path The API path
	 * @return void
	 */
	public function removeModelForPath($model, $path);

	/**
	 * Persist all changes to the database
	 */
	public function persistAllChanges();
}