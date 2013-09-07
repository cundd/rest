<?php
namespace Cundd\Rest\DataProvider;


use Iresults\Core\Iresults;
use TYPO3\CMS\Core\FormProtection\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Utility\EidUtility;
use TYPO3\CMS\Core\Log\LogLevel;

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
	 * The reflection service
	 *
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 * @inject
	 */
	protected $reflectionService;

	/**
	 * The current depth when preparing model data for output
	 * @var int
	 */
	protected $currentModelDataDepth = 0;

	/**
	 * Logger instance
	 * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected $logger;

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
	 * Returns a domain model for the given API path and data
	 * This method will load existing models.
	 *
	 * @param array|string|int $data Data of the new model or it's UID
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithDataForPath($data, $path) {
		$modelClass = $this->getModelClassForPath($path);
		// If no data is given return a new instance
		if (!$data) {
			return $this->getEmptyModelForPath($path);
		} else if (is_scalar($data)) { // If it is a scalar treat it as identity
			return $this->getModelWithIdentityForPath($data, $path);
		}

		$data = $this->prepareModelData($data);
		try {
			$model = $this->propertyMapper->convert($data, $modelClass);
		} catch (\TYPO3\CMS\Extbase\Property\Exception $exception) {
			$model = NULL;

			$message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
			$this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
		}
		return $model;
	}

	/**
	 * Loads the model with the given identifier
	 *
	 * @param mixed		$identifier The identifier
	 * @param string	$path		The path
	 * @return mixed|null|object
	 */
	protected function getModelWithIdentityForPath($identifier, $path) {
		$repository = $this->getRepositoryForPath($path);

		// Tries to fetch the object by UID
		$object = $repository->findByUid($identifier);
		if ($object) {
			return $object;
		}


		// Fetch the first identity property and search the repository for it
		$type = NULL;
		$property = NULL;
		try {
			$classSchema = $this->reflectionService->getClassSchema($this->getModelClassForPath($path));
			$identityProperties = $classSchema->getIdentityProperties();

			$type = reset($identityProperties);
			$property = key($identityProperties);
		} catch (\Exception $exception) {
		}

		switch ($type) {
			case 'string':
				$typeMatching = is_string($identifier);
				break;

			case 'boolean':
				$typeMatching = is_bool($identifier);
				break;

			case 'integer':
				$typeMatching = is_int($identifier);
				break;

			case 'float':
				$typeMatching = is_float($identifier);
				break;

			case 'array':
			default:
				$typeMatching = FALSE;
		}

		if ($typeMatching) {
			$findMethod = 'findOneBy' . ucfirst($property);
			return call_user_func(array($repository, $findMethod), $identifier);
		}
		return NULL;
	}

	/**
	 * Returns a domain model for the given API path and data
	 * Even if the data contains an identifier, the existing model will not be loaded.
	 *
	 * @param array|string|int $data Data of the new model or it's UID
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getNewModelWithDataForPath($data, $path) {
		$uid = NULL;
		// If no data is given return a new instance
		if (!$data) {
			return $this->getEmptyModelForPath($path);
		}

		// Save the identifier and remove it from the data array
		if (isset($data['__identity']) && $data['__identity']) {
			$uid = $data['__identity'];
		} else if (isset($data['uid']) && $data['uid']) {
			$uid = $data['uid'];
		}
		if ($uid) {
			unset($data['__identity']);
			unset($data['uid']);
		}

		// Get a fresh model
		$model = $this->getModelWithDataForPath($data, $path);

		if ($model) {
			// Set the saved identifier
			$model->_setProperty('uid', $uid);
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
			}

			// Transform objects recursive
			if (is_array($properties)) {
				foreach ($properties as $propertyKey => $propertyValue) {
					if (is_object($propertyValue)) {
						if ($propertyValue instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage) {
							$properties[$propertyKey] = $this->getModelDataFromLazyObjectStorage($propertyValue, $propertyKey, $model);
						} else {
							$properties[$propertyKey] = $this->getModelData($propertyValue);
						}
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
	 * Returns the data for the given lazy object storage
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage $lazyObjectStorage
	 * @param string $propertyKey
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @return array<mixed>
	 */
	public function getModelDataFromLazyObjectStorage($lazyObjectStorage, $propertyKey, $model) {
		$returnData = NULL;
		// Get the first level of nested objects
		if ($this->currentModelDataDepth < 1) {
			$this->currentModelDataDepth++;
			$returnData = array();

			// Collect each object of the lazy object storage
			foreach($lazyObjectStorage as $subObject) {
				$returnData[] = $this->getModelData($subObject);
			}
			$this->currentModelDataDepth--;
		} else {
			$returnData = $this->getUriToNestedResource($propertyKey, $model);
		}
		return $returnData;
	}

	/**
	 * Returns the URI of a nested resource
	 *
	 * @param string $resourceKey
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @return string
	 */
	public function getUriToNestedResource($resourceKey, $model) {
		$currentUri = '/rest/';
		$currentUri .= Utility::getPathForClassName(get_class($model)) . '/' . $model->getUid() . '/' . $resourceKey;

		$host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);

		$protocol =  ((!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? 'http' : 'https');

		return $protocol . '://' . $host . $currentUri;
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
	 * Tells the Data Provider to replace the given old model with the new one
	 * in the repository for the given API path
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $oldModel
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $newModel
	 * @param string $path The API path
	 * @return void
	 */
	public function replaceModelForPath($oldModel, $newModel, $path) {
		$repository = $this->getRepositoryForPath($path);
		if ($repository) {
			$repository->replace($oldModel, $newModel);
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

	/**
	 * Returns the logger
	 * @return \TYPO3\CMS\Core\Log\Logger
	 */
	protected function getLogger() {
		if (!$this->logger) {
			$this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
		}
		return $this->logger;
	}
}