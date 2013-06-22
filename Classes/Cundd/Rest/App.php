<?php
namespace Cundd\Rest;

use Bullet\View\Exception;
use Cundd\Rest\DataProvider\Utility;

class App implements \TYPO3\CMS\Core\SingletonInterface {
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
	 * @var \Cundd\Rest\DataProvider\DataProviderInterface
	 */
	protected $dataProvider;

	/**
	 * @var \Bullet\App
	 */
	protected $app;

	/**
	 * @var \Bullet\Request
	 */
	protected $request;

	/**
	 * @var \Cundd\Rest\Authentication\AuthenticationProviderInterface
	 */
	protected $authenticationProvider;

	/**
	 * Initialize
	 */
	public function __construct() {
		$this->app = new \Bullet\App();
		$this->request = new \Bullet\Request(NULL, $this->getUri());

		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	}

	/**
	 * Dispatch the request
	 * @return boolean Returns if the request has been successfully dispatched
	 */
	public function dispatch() {
		$request = $this->request;

		// Checks if the request needs authentication
		if ($this->getAuthenticationProvider()->requestNeedsAuthentication($request)
			&& $this->getAuthenticationProvider()->authenticate() === FALSE) {
			echo new \Bullet\Response('Unauthorized', 401);
			return FALSE;
		}

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
						if (!$model) {
							return 404;
						}
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
							$this->saveModel($model);
						} else {
							return 404;
						}
						return $this->getModelData($model);
					});

					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* REMOVE																	 */
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$this->app->delete(function($request) use($uid) {
						$model = $this->getRepository()->findByUid($uid);
						if ($model) {
							$this->removeModel($model);
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
						$this->saveModel($model);
					} else {
						return 404;
					}
					return $this->getDataProvider()->getModelData($model);
				});

				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* LIST 																	 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$this->app->get(function($request) {
					$repository = $this->getRepository();
					$allModels = $repository->findAll();
					$allModels = iterator_to_array($allModels);
					return array_map(array($this->getDataProvider(), 'getModelData'), $allModels);
				});
			});
		}

		$this->app->path('/', function($request) {
			$greeting = 'What\'s up?';
			$hour = date('H');
			if ($hour <= '10' ) {
				$greeting = 'Good Morning!';
			} else if ($hour >= '23') {
				$greeting = 'Hy! Still awake?';
			}
			return $greeting;
		});

		$success = TRUE;
		$response = $this->app->run($request);
		if ($response->content() instanceof \Exception) {
			$success = FALSE;
			$response = $this->exceptionToResponse($response->content());
		}
		echo $response;
		return $success;
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
		}
		return $this->path;
	}

	/**
	 * Returns the domain model repository for the current API path
	 * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
	 */
	public function getRepository() {
		return $this->getDataProvider()->getRepositoryForPath($this->getPath());
	}

	/**
	 * Returns a new domain model for the given API path and data
	 *
	 * @param array $data Data of the new model
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithData($data) {
		return $this->getDataProvider()->getModelWithDataForPath($data, $this->getPath());
	}

	/**
	 * Returns the data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 */
	public function getModelData($model) {
		return $this->getDataProvider()->getModelData($model);
	}

	/**
	 * Tells the Data Provider to save the given model
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @return void
	 */
	public function saveModel($model) {
		$this->getDataProvider()->saveModelForPath($model, $this->getPath());
	}

	/**
	 * Tells the Data Provider to remove the given model
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @return void
	 */
	public function removeModel($model) {
		$this->getDataProvider()->removeModelForPath($model, $this->getPath());
	}

	/**
	 * Returns the data provider
	 *
	 * @return \Cundd\Rest\DataProvider\DataProviderInterface
	 */
	public function getDataProvider() {
		if (!$this->dataProvider) {
			list($vendor, $extension,) = Utility::getClassNamePartsForPath($this->getPath());

			// Check if an extension provides a Data Provider
			$dataProviderClass  = 'Tx_' . $extension . '_Rest_DataProvider';
			if (!class_exists($dataProviderClass)) {
				$dataProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\DataProvider';
			}
			if (!class_exists($dataProviderClass)) {
				// Get the specific builtin Data Provider
				$dataProviderClass = 'Cundd\\Rest\\DataProvider\\' . $extension . 'DataProvider';
				if (!class_exists($dataProviderClass)) {
					$dataProviderClass = 'Cundd\\Rest\\DataProvider\\DataProviderInterface';
				}
			}
			$this->dataProvider = $this->objectManager->get($dataProviderClass);
		}
		return $this->dataProvider;
	}

	/**
	 * Returns the Authentication Provider
	 * @return \Cundd\Rest\Authentication\AuthenticationProviderInterface
	 */
	public function getAuthenticationProvider() {
		if (!$this->authenticationProvider) {
			$this->authenticationProvider = $this->objectManager->get('Cundd\\Rest\\Authentication\\AuthenticationProviderInterface');
		}
		return $this->authenticationProvider;
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