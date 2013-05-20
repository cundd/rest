<?php
namespace Cundd\Rest;

use Bullet\View\Exception;

class App {
	/**
	 * API path
	 * @var string
	 */
	protected $uri;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * The property mapper
	 *
	 * @var \TYPO3\CMS\Extbase\Property\PropertyMapper
	 */
	protected $propertyMapper;

	/**
	 * @var \Bullet\App
	 */
	protected $app;

	/**
	 * @var \Bullet\Request
	 */
	protected $request;

	/**
	 * Initialize
	 */
	public function __construct() {
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->propertyMapper = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Property\\PropertyMapper');
		$this->app = new \Bullet\App();
		$this->request = new \Bullet\Request(NULL, $this->getUri());
	}

	/**
	 * Dispatch the request
	 * @return void
	 */
	public function dispatch() {
		$request = $this->request;

		/**
		 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
		 */
		$model = NULL;

		// If a path is given
		if ($this->getPath()) {
			$this->app->path($this->getPath(), function($request) {
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* WITH UID 																 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$this->app->param('int', function($request, $uid) {
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* SHOW
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$this->app->get(function($request) use($uid) {
						$model = $this->getRepository()->findByUid($uid);
						return $this->getModelData($model);
					});

					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* UPDATE																	 */
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$this->app->post(function($request) use($uid) {
						$data = $request->post();
						$data['__identity'] = $uid;

						$model = $this->getModelWithData($data);
						if ($model) {
							if ($model->_isNew()) {
								$this->getRepository()->add($model);
							} else {
								$this->getRepository()->update($model);
							}

							$this->persistAllChanges();
						}
						return $this->getModelData($model);
					});

					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* REMOVE																	 */
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$this->app->delete(function($request) use($uid) {
						$model = $this->getRepository()->findByUid($uid);
						if ($model) {
							$this->getRepository()->remove($model);
							$this->persistAllChanges();
						}
						return 200;
					});


				});


				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* CREATE																	 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$this->app->post(function($request) {
					$data = $request->post();

					/**
					 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
					 */
					$model = $this->getModelWithData($data);
					if ($model) {
						if ($model->_isNew()) {
							$this->getRepository()->add($model);
						} else {
							$this->getRepository()->update($model);
						}

						$this->persistAllChanges();
					}
					return $this->getModelData($model);
				});

				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* LIST 																	 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$this->app->get(function($request) {
					$repository = $this->getRepository();
					$allModels = $repository->findAll();
					$allModels = iterator_to_array($allModels);
					return array_map(array($this, 'getModelData'), $allModels);
				});
			});
		}

		$this->app->path('/', function($request) {
			$greeting = '';
			$hour = date('H');
			if ($hour <= '10' ) {
				$greeting = 'Good Morning!';
			} else if ($hour >= '23') {
				$greeting = 'Hy! Still awake?';
			} else {
				$greeting = 'What\'s up?';
			}
			return $greeting;
		});

		$response = $this->app->run($request);
		if ($response->content() instanceof \Exception) {
			$response = $this->exceptionToResponse($response->content());
		}
		echo $response;
	}

	/**
	 * Catch and report the exception, that occurred during the request
	 * @param \Exception $exception
	 */
	public function exceptionToResponse($exception) {
		return new \Bullet\Response($exception->getMessage(), 501);
	}

	/**
	 * @return string
	 */
	public function getUri() {
		if (!$this->uri) {
			$this->uri = $this->getArgument('u');
		}
		return $this->uri;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		if (!$this->path) {
			$uri = $this->getUri();
			$this->path = strtok($uri, '/');
//			$this->path = substr($uri, 0, strpos($uri, '/'));
		}
		return $this->path;
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
	 * Persist all changes to the database
	 */
	protected function persistAllChanges() {
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');
		$persistenceManager->persistAll();
	}

	/**
	 * @param string $name Argument name
	 * @param int $filter Filter for the input
	 * @param mixed $default Default value to use if no argument with the given name exists
	 * @return mixed
	 */
	protected function getArgument($name, $filter = FILTER_SANITIZE_STRING, $default = NULL) {
		$argument = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($name);
		$argument = filter_var($argument, $filter);
		if ($argument === NULL) {
			$argument = $default;
		}
		return $argument;
	}
}


?>