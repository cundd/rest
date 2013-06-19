<?php
namespace Cundd\Rest\DataProvider;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Utility\EidUtility;

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
		list($vendor, $extension, $model) = Utility::getClassNamePartsForPath($path);
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
		list($vendor, $extension, $model) = Utility::getClassNamePartsForPath($path);
		$modelClass = 'Tx_' . $extension . '_Domain_Model_' . $model;
		if (!class_exists($modelClass)) {
			$modelClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Model\\' . $model;
		}
		return $modelClass;
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

		$data = $this->prepareModelData($data);
		try {
			$model = $this->propertyMapper->convert($data, $modelClass);
		} catch (\TYPO3\CMS\Extbase\Property\Exception $e) {
			$model = NULL;
		}
		return $model;
	}

	/**
	 * Returns a new domain model for the given API path
	 *
	 * @param string $path
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelForPath($path) {
		return $this->getModelWithDataForPath(array(), $path);
	}

	/**
	 * Returns a new domain model for the given API path points to
	 *
	 * @param string $path API path to get the model for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getEmptyModelForPath($path) {
		$modelClass = $this->getModelClassForPath($path);
		return $this->objectManager->get($modelClass);
	}

	/**
	 * Returns the data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @return array<mixed>
	 */
	public function getModelData($model) {
		$properties = NULL;
		if (is_object($model)) {
			// Get the data from the model
			if (method_exists($model, 'jsonSerialize')) {
				$properties = $model->jsonSerialize();
			} else if ($model instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
				$properties = $model->_getProperties();
//			} else if ($model instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage) {
//				// TODO: handle the lazy object storage
//
//				/** @var \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage $lazyObjectStorage */
//				$lazyObjectStorage = $model;
////				$lazyObjectStorage->count();
////
////
////				$properties = array(
////					'uri' => Utility::getPathForClassName(get_class(reset(iterator_to_array($model)))),
////					'path' => Utility::getPathForClassName(get_class(reset(iterator_to_array($model)))),
////					'__class' => (get_class(reset(iterator_to_array($model))))
////				);
//				$properties = array(
//					'__info' 	=> 'Lazy loaded',
//					'__class' 	=> ''
//				);
			}

			// Transform objects recursive
			foreach ($properties as $propertyKey => $propertyValue) {
				if (is_object($propertyValue)) {
					if ($propertyValue instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage) {
						$properties[$propertyKey] = $this->getUriToNestedResource($propertyKey);
					} else {
						$properties[$propertyKey] = $this->getModelData($propertyValue);
					}
				}
			}

			if ($properties && !isset($properties['__class'])) {
				$properties['__class'] = get_class($model);
			}
		}

		if (!$properties) {
			$properties = $model;
		}
		return $properties;
	}

	/**
	 * Returns the URI of a nested resource
	 *
	 * @param string $resourceKey
	 * @return string
	 */
	public function getUriToNestedResource($resourceKey) {
		// TODO: fix this
		$currentUri = $_SERVER['REQUEST_URI'];
		if (substr($currentUri, -1) !== '/') {
			$currentUri .= '/';
		}
		return 'http://' . $_SERVER['HTTP_HOST'] . $currentUri . $resourceKey;
	}

	/**
	 * Returns the property data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $propertyKey
	 * @return mixed
	 */
	public function getModelProperty($model, $propertyKey) {
		$propertyValue = $model->_getProperty($propertyKey);
		if (is_object($propertyValue)) {
			if ($propertyValue instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage) {
				$propertyValue = iterator_to_array($propertyValue);

				// Transform objects recursive
				foreach ($propertyValue as $childPropertyKey => $childPropertyValue) {
					if (is_object($childPropertyValue)) {
						$propertyValue[$childPropertyKey] = $this->getModelData($childPropertyValue);
					}
				}
				$propertyValue = array_values($propertyValue);
			} else {
				$propertyValue = $this->getModelData($propertyValue);
			}
		} else if (!$propertyValue) {
			return NULL;
		}
		return $propertyValue;
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

	/**
	 * Prepares the given data before transforming it to a model
	 *
	 * @param $data
	 * @return array
	 */
	protected function prepareModelData($data) {
		return $data;
	}
}