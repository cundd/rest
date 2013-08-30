<?php
namespace Cundd\Rest;

use Bullet\View\Exception;
use Cundd\Rest\DataProvider\Utility;
use Iresults\Core\Iresults;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;


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
	 * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected $logger;

	/**
	 * The response format
	 * @var string
	 */
	protected $format;

	/**
	 * Configuration provider
	 * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
	 */
	protected $configurationProvider;

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
			$this->logRequest('path: "' . $this->getPath() . '" method: "' . $request->method() . '"' );

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
					/* REPLACE																	 */
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$replaceCallback = function($request) use($uid, $dispatcher, $app) {
						$data = $request->post();
						$data['__identity'] = $uid;
						$dispatcher->logRequest('replace request', array('body' => $data));

						$oldModel = $dispatcher->getModelWithData($uid);
						$newModel = $dispatcher->getNewModelWithData($data);

						if (!$oldModel) {
							return 404;
						}
						if (!$newModel) {
							return 400;
						}
						$dispatcher->replaceModel($oldModel, $newModel);
						return $dispatcher->getModelData($newModel);
					};
					$app->put($replaceCallback);
					$app->post($replaceCallback);

					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					/* UPDATE																	 */
					/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
					$updateCallback = function($request) use($uid, $dispatcher, $app) {
						$data = $request->post();
						$data['__identity'] = $uid;
						$dispatcher->logRequest('update request', array('body' => $data));

						$model = $dispatcher->getModelWithData($data);

						if (!$model) {
							return 404;
						}

						$dispatcher->saveModel($model);
						return $dispatcher->getModelData($model);
					};
					$app->patch($updateCallback);

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
					$dispatcher->logRequest('create request', array('body' => $data));

					/**
					 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
					 */
					$model = $dispatcher->getModelWithData($data);
					if (!$model) {
						return 400;
					}

					$dispatcher->saveModel($model);
					return $dispatcher->getDataProvider()->getModelData($model);
				});

				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				/* LIST 																	 */
				/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
				$app->get(function($request) use($dispatcher, $app) {
					$repository = $dispatcher->getRepository();
					$allModels = $repository->findAll();
					$allModels = iterator_to_array($allModels);

					$result = array_map(array($dispatcher->getDataProvider(), 'getModelData'), $allModels);
					if ($dispatcher->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
						return array(
							$dispatcher->getOriginalPath() => $result
						);
					}
					return $result;
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

		$response->content($response->content());

		if ($response->content() instanceof \Exception) {
			$success = FALSE;

			$exception = $response->content();
			$this->logException($exception);
			$response = $this->exceptionToResponse($exception);
		}

		$responseString = $response . '';
		$this->logResponse('response: ' . $response->status(), array('response' => '' . $responseString));
		echo $responseString;
		return $success;
	}

	/**
	 * Catch and report the exception, that occurred during the request
	 * @param \Exception $exception
	 * @return \Bullet\Response
	 */
	public function exceptionToResponse($exception) {
		return new \Bullet\Response($exception->getMessage(), 501);
	}

	/**
	 * Returns the request
	 * @return \Cundd\Rest\Request
	 */
	public function getRequest() {
		if (!$this->request) {
			$format = '';
			$uri = $this->getUri($format);
			$this->request = new Request(NULL, $uri);
			$this->request->injectConfigurationProvider($this->getConfigurationProvider());
			if ($format) {
				$this->request->format($format);
			}

		}
		return $this->request;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->getRequest()->path();
	}

	/**
	 * @return string
	 */
	public function getOriginalPath() {
		return $this->getRequest()->originalPath();
	}

	/**
	 * Returns the domain model repository for the current API path
	 * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
	 */
	public function getRepository() {
		return $this->getDataProvider()->getRepositoryForPath($this->getPath());
	}

	/**
	 * Returns a domain model for the given API path and data
	 * This method will load existing models.
	 *
	 * @param array $data Data of the new model
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithData($data) {
		return $this->getDataProvider()->getModelWithDataForPath($data, $this->getPath());
	}

	/**
	 * Returns a new domain model for the given API path and data
	 * Even if the data contains an identifier, the existing model will not be loaded.
	 *
	 * @param array $data Data of the new model
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getNewModelWithData($data) {
		return $this->getDataProvider()->getNewModelWithDataForPath($data, $this->getPath());
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
	 * Tells the Data Provider to replace the given old model with the new one
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $oldModel
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $newModel
	 * @return void
	 */
	public function replaceModel($oldModel, $newModel) {
		$this->getDataProvider()->replaceModelForPath($oldModel, $newModel, $this->getPath());
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
			if (!class_exists($authenticationProviderClass)) {
				$authenticationProviderClass = 'Cundd\\Rest\\Authentication\\ConfigurationBasedAuthenticationProvider';
			}
			$this->authenticationProvider = $this->objectManager->get($authenticationProviderClass);
			$this->authenticationProvider->setRequest($this->getRequest());
		}
		return $this->authenticationProvider;
	}

	/**
	 * Returns the URI
	 * @param string $format Reference to be filled with the request format
	 * @return string
	 */
	public function getUri(&$format = '') {
		if (!$this->uri) {
			$uri = $this->getArgument('u', FILTER_SANITIZE_URL);
			if (!$uri) {
				$uri = substr($_SERVER['REQUEST_URI'], 6);
				$uri = filter_var($uri, FILTER_SANITIZE_URL);
			}

			// Strip the format from the URI
			$resourceName = basename($uri);
			$lastDotPosition = strrpos($resourceName, '.');
			if ($lastDotPosition !== FALSE) {
				$newUri = '';
				if ($uri !== $resourceName) {
					$newUri = dirname($uri) . '/';
				}
				$newUri .= substr($resourceName, 0, $lastDotPosition);
				$uri = $newUri;

				$format = substr($resourceName, $lastDotPosition + 1);
			}
			$this->uri = $uri;
		}
		return $this->uri;
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
	 * Returns the configuration provider
	 * @return \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
	 */
	public function getConfigurationProvider() {
		if (!$this->configurationProvider) {
			$this->configurationProvider = $this->objectManager->get('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider');
		}
		return $this->configurationProvider;
	}

	/**
	 * Returns the logger
	 * @return \TYPO3\CMS\Core\Log\Logger
	 */
	public function getLogger() {
		if (!$this->logger) {
			$this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
		}
		return $this->logger;
	}

	/**
	 * Logs the given request message and data
	 * @param string $message
	 * @param array $data
	 */
	protected function logRequest($message, $data = NULL) {
		if ($this->getExtensionConfiguration('logRequests')) {
			$this->log($message, $data);
		}
	}

	/**
	 * Logs the given response message and data
	 * @param string $message
	 * @param array $data
	 */
	protected function logResponse($message, $data = NULL) {
		if ($this->getExtensionConfiguration('logResponse')) {
			$this->log($message, $data);
		}
	}

	/**
	 * Logs the given exception
	 * @param \Exception $exception
	 */
	protected function logException($exception) {
		$message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
		$this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
	}

	/**
	 * Logs the given message and data
	 * @param string $message
	 * @param array $data
	 */
	protected function log($message, $data = NULL) {
		if ($data) {
			$this->getLogger()->log(LogLevel::DEBUG, $message, $data);
		} else {
			$this->getLogger()->log(LogLevel::DEBUG, $message);
		}
	}

	/**
	 * Returns the extension configuration for the given key
	 * @param $key
	 * @return mixed
	 */
	protected function getExtensionConfiguration($key) {
		// Read the configuration from the globals
		static $configuration;
		if (!$configuration) {
			if (isset($GLOBALS['TYPO3_CONF_VARS'])
				&& isset($GLOBALS['TYPO3_CONF_VARS']['EXT'])
				&& isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'])
				&& isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rest'])
			) {
				$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rest']);
			}
		}

		if (isset($configuration[$key])) {
			return $configuration[$key];
		}
		return NULL;
	}
}
?>