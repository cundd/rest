<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest;

use Bullet\Response;
use Bullet\View\Exception;
use Cundd\Rest\Cache\Cache;
use Cundd\Rest\DataProvider\Utility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\SingletonInterface;
use Cundd\Rest\Access\AccessControllerInterface;

/**
 * Main dispatcher of REST requests
 *
 * The dispatcher will first check the access to the requested resource. Then it will check the cache for a stored
 * response for the current request. If no cached response was found,
 *
 * @package Cundd\Rest
 */
class Dispatcher implements SingletonInterface {
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
	 *
	 * @var \Cundd\Rest\Dispatcher
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
	 * @param Response $responsePointer Reference to be filled with the response
	 * @return boolean Returns if the request has been successfully dispatched
	 */
	public function dispatch(Request $request = NULL, Response &$responsePointer = NULL) {
		if ($request) {
			$this->request = $request;
			$this->objectManager->reassignRequest();
		} else {
			$request = $this->getRequest();
		}

		if (!$this->getPath()) {
			return $this->greet();
		}

		// Checks if the request needs authentication
		switch ($this->objectManager->getAccessController()->getAccess()) {
			case AccessControllerInterface::ACCESS_ALLOW:
				break;

			case AccessControllerInterface::ACCESS_UNAUTHORIZED:
				echo new Response('Unauthorized', 401);
				return FALSE;

			case AccessControllerInterface::ACCESS_DENY:
			default:
				echo new Response('Forbidden', 403);
				return FALSE;
		}

		/** @var Cache $cache */
		$cache = $this->objectManager->getCache();
		$response = $cache->getCachedValueForRequest($request);

		$success = TRUE;

		// If no cached response exists
		if (!$response) {

			// If a path is given let the handler build up the routes
			if ($this->getPath()) {
				$this->logRequest('path: "' . $this->getPath() . '" method: "' . $request->method() . '"');
				$this->objectManager->getHandler()->configureApiPaths();
			}

			// Let Bullet PHP do the hard work
			$response = $this->app->run($request);

//			$response->content($response->content());

			// Handle exceptions
			if ($response->content() instanceof \Exception) {
				$success = FALSE;

				$exception = $response->content();
				$this->logException($exception);
				$response = $this->exceptionToResponse($exception);
			}

			// Cache the response
			$cache->setCachedValueForRequest($request, $response);
		}

		$responsePointer = $response;
		$responseString = (string)$response;
		$this->logResponse('response: ' . $response->status(), array('response' => '' . $responseString));
		echo $responseString;
		return $success;
	}

	/**
	 * Print the greeting
	 * @return boolean Returns if the request has been successfully dispatched
	 */
	public function greet() {
		/** @var \Cundd\Rest\Request $request */
		$request = $this->getRequest();

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

		$response = $this->app->run($request);

		$responseString = (string)$response;
		$this->logResponse('response: ' . $response->status(), array('response' => '' . $responseString));
		echo $responseString;
		return TRUE;
	}

	/**
	 * Catch and report the exception, that occurred during the request
	 * @param \Exception $exception
	 * @return Response
	 */
	public function exceptionToResponse($exception) {
		if ($_SERVER['SERVER_ADDR'] === '127.0.0.1') {
			return new Response('Sorry! Something is wrong. Exception code: ' . $exception->getCode(), 501);
		}
		return new Response('Sorry! Something is wrong. Exception code: ' . $exception, 501);
	}

	/**
	 * Returns the request
	 * @return \Cundd\Rest\Request
	 */
	public function getRequest() {
		if (!$this->request) {
			$format = '';
			$uri = $this->getUri($format);

			/*
			 * Transform Document URLs
			 * @Todo: Make this better
			 */
			if (substr($uri, 0, 9) === 'Document/') {
				$uri = 'Document-' . substr($uri, 9);
			}
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
	 * Returns the sent data
	 * @return mixed
	 */
	public function getSentData() {
		$request = $this->getRequest();

		/** @var \Cundd\Rest\Request $request */
		$data = $request->post();
		/*
		 * If no form url-encoded body is sent check if a JSON
		 * payload is sent with the singularized root object key as
		 * the payload's root object key
		 */
		if (!$data) {
			$data = $request->get(
				Utility::singularize($this->getRootObjectKey())
			);
			if (!$data) {
				$data = json_decode($request->raw(), TRUE);
			}
		}
		return $data;
	}

	/**
	 * Returns the key to use for the root object if addRootObjectForCollection
	 * is enabled
	 *
	 * @return string
	 */
	public function getRootObjectKey() {
		$originalPath = $this->getOriginalPath();
		/*
		 * Transform Document URLs
		 * @Todo: Make this better
		 */
		if (substr($originalPath, 0, 9) === 'Document-') {
			$originalPath = substr($originalPath, 9);
		}
		return $originalPath;
	}

	/**
	 * Returns the Bullet App
	 *
	 * @return \Bullet\App
	 */
	public function getApp() {
		return $this->app;
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
	 *
	 * @return \Cundd\Rest\Dispatcher
	 */
	static public function getSharedDispatcher() {
		return self::$sharedDispatcher;
	}
}
?>
