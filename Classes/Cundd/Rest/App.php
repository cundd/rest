<?php
namespace Cundd\Rest;

use Bullet\View\Exception;
use Cundd\Rest\DataProvider\Utility;
use Iresults\Core\Iresults;

class App implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * API path
	 * @var string
	 */
	protected $uri;

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
	 * @var \Cundd\Rest\Request
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
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	}

	/**
	 * Dispatch the request
	 * @return boolean Returns if the request has been successfully dispatched
	 */
	public function dispatch() {
		$request = $this->getRequest();

		// Checks if the request needs authentication
		if ($this->getAuthenticationProvider()->requestNeedsAuthentication()) {
			try {
				$isAuthenticated = $this->getAuthenticationProvider()->authenticate();
			} catch (\Exception $exception) {
				$this->logException($exception);
				$isAuthenticated = FALSE;
			}
			if ($isAuthenticated === FALSE) {
				echo new \Bullet\Response('Unauthorized', 401);
				return FALSE;
			}
		}

		$dispatcher = $this;
		$app = $this->app;


//		header('Content-Type: text/html; charset=utf-8');
//		echo '<html>';
//		echo '<head><title></title></head>';
//		echo '<body>';
//		Iresults::pd($configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT));
//		Iresults::pd($configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS));
//		Iresults::pd($configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK));
//		echo '</body>';
//		echo '</html>';
//		die;

		/**
		 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
		 */
		$model = NULL;

		// If a path is given
		if ($this->getPath()) {
			$app->path($this->getPath(), function($request) use($dispatcher, $app) {
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* WITH UID 																 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$app->param('int', function($request, $uid) use($dispatcher, $app) {
					$app->param('slug', function ($request, $propertyKey) use($uid, $dispatcher, $app) {
						$model = $dispatcher->getModelWithData($uid);
						if (!$model) {
							return 404;
						}
						return $dispatcher->getModelProperty($model, $propertyKey);
					});

					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* SHOW
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$app->get(function($request) use($uid, $dispatcher, $app) {
						$model = $dispatcher->getModelWithData($uid);
						if (!$model) {
							return 404;
						}
						return $dispatcher->getModelData($model);
					});

					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* UPDATE																	 */
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$app->post(function($request) use($uid, $dispatcher, $app) {
						$data = $request->post();
						$data['__identity'] = $uid;

						$model = $dispatcher->getModelWithData($data);
						if ($model) {
							$dispatcher->saveModel($model);
						} else {
							return 404;
						}
						return $dispatcher->getModelData($model);
					});

					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* REMOVE																	 */
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$app->delete(function($request) use($uid, $dispatcher, $app) {
						$model = $dispatcher->getModelWithData($uid);
						if ($model) {
							$dispatcher->removeModel($model);
						}
						return 200;
					});
				});

				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* CREATE																	 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$app->post(function($request) use($dispatcher, $app) {
					$data = $request->post();

					/**
					 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
					 */
					$model = $dispatcher->getModelWithData($data);
					if ($model) {
						$dispatcher->saveModel($model);
					} else {
						return 404;
					}
					return $dispatcher->getDataProvider()->getModelData($model);
				});

				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* LIST 																	 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$app->get(function($request) use($dispatcher, $app) {
					$repository = $dispatcher->getRepository();
					$allModels = $repository->findAll();
					$allModels = iterator_to_array($allModels);
					return array_map(array($dispatcher->getDataProvider(), 'getModelData'), $allModels);
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
	 * @return \Cundd\Rest\Request
	 */
	public function getRequest() {
		if (!$this->request) {
			$this->request = new Request(NULL, $this->getArgument('u'));
		}
		return $this->request;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->getRequest()->getPath();
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
	 * Returns the property data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $propertyKey
	 * @return mixed
	 */
	public function getModelProperty($model, $propertyKey) {
		return $this->dataProvider->getModelProperty($model, $propertyKey);
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
			// Get the specific builtin Data Provider
			if (!class_exists($dataProviderClass)) {
				$dataProviderClass = 'Cundd\\Rest\\DataProvider\\' . $extension . 'DataProvider';
				// Get the default Data Provider
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
			list($vendor, $extension,) = Utility::getClassNamePartsForPath($this->getPath());

			// Check if an extension provides a Authentication Provider
			$authenticationProviderClass  = 'Tx_' . $extension . '_Rest_AuthenticationProvider';
			if (!class_exists($authenticationProviderClass)) {
				$authenticationProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AuthenticationProvider';
			}

			// Use the configuration based Authentication Provider
			$authenticationProviderClass = 'Cundd\\Rest\\Authentication\\ConfigurationBasedAuthenticationProvider';

			// Get the specific builtin Authentication Provider
			if (!class_exists($authenticationProviderClass)) {
				$authenticationProviderClass = 'Cundd\\Rest\\Authentication\\' . $extension . 'AuthenticationProvider';
				// Get the default Authentication Provider
				if (!class_exists($authenticationProviderClass)) {
					$authenticationProviderClass = 'Cundd\\Rest\\Authentication\\AuthenticationProviderInterface';
				}
			}
			$this->authenticationProvider = $this->objectManager->get($authenticationProviderClass);
			$this->authenticationProvider->setRequest($this->getRequest());
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


	/**
	 * Logs the given exception
	 *
	 * @TODO: Implement
	 * @param $exception
	 */
	protected function logException($exception) {}
}
?>