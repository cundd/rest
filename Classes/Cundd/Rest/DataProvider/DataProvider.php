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
	 * Returns a new domain model for the given API path
	 *
	 * @param array $data Data of the new model
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelForPath($path) {
		return $this->getModelWithDataForPath(array(), $path);
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
		try {
			$model = $this->propertyMapper->convert($data, $modelClass);
		} catch (\TYPO3\CMS\Extbase\Property\Exception $e) {
			$model = NULL;
		}
		return $model;
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
		if (strpos($path, '_') !== FALSE) {
			$path = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($path);
		}
		$parts = explode('-', $path);
		if (count($parts) < 3) {
			array_unshift($parts, '');
		}
		$parts = array_map(function($part) {return ucfirst($part);}, $parts);
		return $parts;
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
	 * Adds or updates the given model in the repository for the
	 * given API path
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $path The API path
	 * @return void
	 */
	public function saveModelForPath($model, $path) {
		$repository = $this->getRepositoryForPath($path);
		if ($repository) {
			if ($model->_isNew()) {
				$repository->add($model);
			} else {
				$repository->update($model);
			}
			$this->persistAllChanges();
		}
	}

	/**
	 * Adds or updates the given model in the repository for the
	 * given API path
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $path The API path
	 * @return void
	 */
	public function removeModelForPath($model, $path) {
		$repository = $this->getRepositoryForPath($path);
		if ($repository) {
			$repository->remove($model);
			$this->persistAllChanges();
		}
	}

	/**
	 * Persist all changes to the database
	 */
	public function persistAllChanges() {
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');
		$persistenceManager->persistAll();
	}
}