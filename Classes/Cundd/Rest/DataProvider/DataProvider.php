<?php
namespace Cundd\Rest\DataProvider;


class DataProvider implements DataProviderInterface {
	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * The property mapper
	 *
	 * @var \TYPO3\CMS\Extbase\Property\PropertyMapper
	 * @inject
	 */
	protected $propertyMapper;

	/**
	 * Returns the domain model repository class name for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return string
	 */
	public function getRepositoryClassForPath($path) {
		list($vendor, $extension, $model) = $this->getClassNamePartsForPath($path);
		$repositoryClass = 'Tx_' . $extension . '_Domain_Repository_' . $model . 'Repository';
		if (!class_exists($repositoryClass)) {
			$repositoryClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Repository\\' . $model . 'Repository';
		}
		return $repositoryClass;
	}

	/**
	 * Returns the domain model repository for the current API path
	 * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
	 */
	public function getRepository() {
		$repository = $this->getRepositoryForPath($this->getPath());
		return $repository;
	}

	/**
	 * Returns the domain model repository for the models the given API path points to
	 *
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
	 */
	public function getRepositoryForPath($path) {
		$repositoryClass = $this->getRepositoryClassForPath($path);
		$repository = $this->objectManager->get($repositoryClass);
		$repository->setDefaultQuerySettings($this->objectManager->get('Cundd\\Rest\\Persistence\\Generic\\RestQuerySettings'));
		return $repository;
	}

	/**
	 * Returns the domain model class name for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return string
	 */
	public function getModelClassForPath($path) {
		list($vendor, $extension, $model) = $this->getClassNamePartsForPath($path);
		$modelClass = 'Tx_' . $extension . '_Domain_Model_' . $model;
		if (!class_exists($modelClass)) {
			$modelClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Model\\' . $model;
		}
		return $modelClass;
	}

	/**
	 * Returns a new domain model for the current API path and data
	 *
	 * @param array $data Data of the new model
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithData($data) {
		return $this->getModelWithDataForPath($data, $this->getPath());
	}

	/**
	 * Returns a new domain model for the given API path and data
	 *
	 * @param array $data Data of the new model
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithDataForPath($data, $path) {
		$modelClass = $this->getModelClassForPath($path);
		if (!$data) {
			return $this->getEmptyModelForPath($path);
		}
		return $this->propertyMapper->convert($data, $modelClass);
	}

	/**
	 * Returns an array of class name parts including vendor, extension
	 * and domain model
	 *
	 * Example:
	 *   array(
	 *     Vendor
	 *     MyExt
	 *     MyModel
	 *   )
	 * @param $path
	 * @return array
	 */
	public function getClassNamePartsForPath($path) {
		$parts = explode('_', $path);
		if (count($parts) < 3) {
			array_unshift($parts, '');
		}
		return $parts;
	}

	/**
	 * Returns the properties of the domain model
	 * @return array<string>
	 */
	protected function _getDomainModelsProperties() {
		static $properties;
		if (!$properties) {
			// Get the Repository domain object properties
			$properties = $this->getEmptyModel();
			$properties = array_keys($properties->_getProperties());
		}
		return $properties;
	}

	/**
	 * Returns a new domain model for the current API path
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getEmptyModel() {
		return $this->getEmptyModelForPath($this->getPath());
	}

	/**
	 * Returns a new domain model for the given API path points to
	 *
	 * @param string $path API path to get the model for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getEmptyModelForPath($path) {
		$modelClass = $this->getModelClassForPath($path);
		return $this->objectManager->create($modelClass);
	}

	/**
	 * Returns the data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 */
	public function getModelData($model) {
		$properties = NULL;
		if (is_object($model) && $model instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
			$properties = $model->_getProperties();
			$properties['__class'] = get_class($model);
		} else {
			$properties = $model;
		}
		return $properties;
	}

	/**
	 * Persist all changes to the database
	 */
	public function persistAllChanges() {
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');
		$persistenceManager->persistAll();
	}
}