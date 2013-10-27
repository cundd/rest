<?php
namespace Cundd\Rest;

use Bullet\View\Exception;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\SingletonInterface;
use Cundd\Rest\Access\AccessControllerInterface;


class App implements SingletonInterface {
	/**
	 * API path
	 * @var string
	 */
	protected $uri;

	/**
	 * @var \Cundd\Rest\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \Bullet\App
	 */
	protected $app;

	/**
	 * @var \Cundd\Rest\Request
	 */
	protected $request;

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
	 * The shared instance
	 * @var \Cundd\Rest\App
	 */
	static protected $sharedDispatcher;

	/**
	 * Initialize
	 */
	public function __construct() {
		$this->app = new \Bullet\App();
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Cundd\\Rest\\ObjectManager');
		$this->objectManager->setDispatcher($this);

		self::$sharedDispatcher = $this;
	}

	/**
	 * Dispatch the request
	 *
	 * @param \Cundd\Rest\Request $request Overwrite the request
	 * @param \Bullet\Response $responsePointer Reference to be filled with the response
	 * @return boolean Returns if the request has been successfully dispatched
	 */
	public function dispatch($request = NULL, &$responsePointer = NULL) {
		if ($request) {
			$this->request = $request;
			$this->objectManager->reassignRequest();
		} else {
			$request = $this->getRequest();
		}

		// If a path is given
		if ($this->getPath()) {
			// Checks if the request needs authentication
			switch ($this->objectManager->getAccessController()->getAccess()) {
				case AccessControllerInterface::ACCESS_ALLOW:
					break;

				case AccessControllerInterface::ACCESS_UNAUTHORIZED:
					echo $responsePointer = new \Bullet\Response('Unauthorized', 401);
					return FALSE;

				case AccessControllerInterface::ACCESS_DENY:
				default:
					echo $responsePointer = new \Bullet\Response('Forbidden', 403);
					return FALSE;
			}

			$this->configureApiPaths();
		}

		// Defaults
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

		$responsePointer = $response;
		$responseString = $response . '';
		$this->logResponse('response: ' . $response->status(), array('response' => '' . $responseString));
		echo $responseString;
		return $success;
	}

	/**
	 * Configure the API paths
	 */
	protected function configureApiPaths() {
		/**
		 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
		 */

		$request = $this->getRequest();
		$dispatcher = $this;
		$app = $this->app;

		$this->logRequest('path: "' . $this->getPath() . '" method: "' . $request->method() . '"' );
		$app->path($this->getPath(), function($request) use($dispatcher, $app) {
			/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
			/* WITH UID 																 */
			/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
			$app->param('slug', function($request, $uid) use($dispatcher, $app) {
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
					/** @var \Cundd\Rest\Request $request */
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
					/** @var \Cundd\Rest\Request $request */
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
				/** @var \Cundd\Rest\Request $request */
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
				return $dispatcher->getObjectManager()->getDataProvider()->getModelData($model);
			});

			/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
			/* LIST 																	 */
			/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
			$app->get(function($request) use($dispatcher, $app) {
				$repository = $dispatcher->getRepository();
				$allModels = $repository->findAll();
				$allModels = iterator_to_array($allModels);

				$result = array_map(array($dispatcher->getObjectManager()->getDataProvider(), 'getModelData'), $allModels);
				if ($dispatcher->getObjectManager()->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
					return array(
						$dispatcher->getOriginalPath() => $result
					);
				}
				return $result;
			});
		});
	}

	/**
	 * Catch and report the exception, that occurred during the request
	 * @param \Exception $exception
	 * @return \Bullet\Response
	 */
	public function exceptionToResponse($exception) {
		return new \Bullet\Response('Sorry! Something is wrong. Exception code: ' . $exception->getCode(), 501);
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
			$this->request->injectConfigurationProvider($this->objectManager->getConfigurationProvider());
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
		return $this->objectManager->getDataProvider()->getRepositoryForPath($this->getPath());
	}

	/**
	 * Returns a domain model for the given API path and data
	 * This method will load existing models.
	 *
	 * @param array $data Data of the new model
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithData($data) {
		return $this->objectManager->getDataProvider()->getModelWithDataForPath($data, $this->getPath());
	}

	/**
	 * Returns a new domain model for the given API path and data
	 * Even if the data contains an identifier, the existing model will not be loaded.
	 *
	 * @param array $data Data of the new model
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getNewModelWithData($data) {
		return $this->objectManager->getDataProvider()->getNewModelWithDataForPath($data, $this->getPath());
	}

	/**
	 * Returns the data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 */
	public function getModelData($model) {
		return $this->objectManager->getDataProvider()->getModelData($model);
	}

	/**
	 * Returns the property data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $propertyKey
	 * @return mixed
	 */
	public function getModelProperty($model, $propertyKey) {
		return $this->objectManager->getDataProvider()->getModelProperty($model, $propertyKey);
	}

	/**
	 * Tells the Data Provider to save the given model
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @return void
	 */
	public function saveModel($model) {
		$this->objectManager->getDataProvider()->saveModelForPath($model, $this->getPath());
	}

	/**
	 * Tells the Data Provider to replace the given old model with the new one
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $oldModel
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $newModel
	 * @return void
	 */
	public function replaceModel($oldModel, $newModel) {
		$this->objectManager->getDataProvider()->replaceModelForPath($oldModel, $newModel, $this->getPath());
	}

	/**
	 * Tells the Data Provider to remove the given model
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @return void
	 */
	public function removeModel($model) {
		$this->objectManager->getDataProvider()->removeModelForPath($model, $this->getPath());
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
	 * Returns the object manager
	 *
	 * @return \Cundd\Rest\ObjectManager
	 */
	public function getObjectManager() {
		return $this->objectManager;
	}

	/**
	 * Returns the data provider
	 * @return \Cundd\Rest\DataProvider\DataProviderInterface
	 */
	public function getDataProvider() {
		return $this->objectManager->getDataProvider();
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
	public function logRequest($message, $data = NULL) {
		if ($this->getExtensionConfiguration('logRequests')) {
			$this->log($message, $data);
		}
	}

	/**
	 * Logs the given response message and data
	 * @param string $message
	 * @param array $data
	 */
	public function logResponse($message, $data = NULL) {
		if ($this->getExtensionConfiguration('logResponse')) {
			$this->log($message, $data);
		}
	}

	/**
	 * Logs the given exception
	 * @param \Exception $exception
	 */
	public function logException($exception) {
		$message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
		$this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
	}

	/**
	 * Logs the given message and data
	 * @param string $message
	 * @param array $data
	 */
	public function log($message, $data = NULL) {
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

	/**
	 * Returns the shared dispatcher instance
	 * @return \Cundd\Rest\App
	 */
	static public function getSharedDispatcher() {
		return self::$sharedDispatcher;
	}

}
?>