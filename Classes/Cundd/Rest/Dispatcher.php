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
use Cundd\Rest\Dispatcher\ApiConfigurationInterface;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\SingletonInterface;
use Cundd\Rest\Access\AccessControllerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Main dispatcher of REST requests
 *
 * The dispatcher will first check the access to the requested resource. Then it will check the cache for a stored
 * response for the current request. If no cached response was found,
 *
 * @package Cundd\Rest
 */
class Dispatcher implements SingletonInterface, ApiConfigurationInterface, DispatcherInterface {
    /**
     * @var \Cundd\Rest\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Bullet\App
     */
    protected $app;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

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

        $this->objectManager = GeneralUtility::makeInstance('Cundd\\Rest\\ObjectManager');
        $this->requestFactory = $this->objectManager->getRequestFactory();
        $this->responseFactory = $this->objectManager->getResponseFactory();
        $this->registerSingularToPlural();

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
            $this->requestFactory->registerCurrentRequest($request);
            $this->objectManager->reassignRequest();
        } else {
            $request = $this->requestFactory->getRequest();
        }

        $requestPath = $request->path();
        if (!$requestPath) {
            return $this->greet();
        }

        // Checks if the request needs authentication
        switch ($this->objectManager->getAccessController()->getAccess()) {
            case AccessControllerInterface::ACCESS_ALLOW:
                break;

            case AccessControllerInterface::ACCESS_UNAUTHORIZED:
                echo $this->responseFactory->createErrorResponse('Unauthorized', 401);
                return FALSE;

            case AccessControllerInterface::ACCESS_DENY:
            default:
                echo $this->responseFactory->createErrorResponse('Forbidden', 403);
                return FALSE;
        }

        /** @var Cache $cache */
        $cache = $this->objectManager->getCache();
        $response = $cache->getCachedValueForRequest($request);

        $success = TRUE;

        // If no cached response exists
        if (!$response) {

            // If a path is given let the handler build up the routes
            if ($requestPath) {
                $this->logRequest(sprintf('path: "%s" method: "%s"', $requestPath, $request->method()));
                $this->objectManager->getHandler()->configureApiPaths();
            }

            // Let Bullet PHP do the hard work
            $response = $this->app->run($request);

            // Handle exceptions
            if ($response->content() instanceof \Exception) {
                $success = FALSE;

                $exception = $response->content();
                $this->logException($exception);
                $response = $this->exceptionToResponse($exception);
            } else {
                // Cache the response
                $cache->setCachedValueForRequest($request, $response);
            }
        }

        // Additional custom headers
        $additionalResponseHeaders = $this->objectManager->getConfigurationProvider()->getSetting('responseHeaders', array());
        if (is_array($additionalResponseHeaders) && count($additionalResponseHeaders)) {
            foreach ($additionalResponseHeaders as $responseHeaderType => $value) {
                if (is_string($value)) {
                    $response->header($responseHeaderType, $value);
                } elseif (is_array($value) && array_key_exists('userFunc', $value)) {
                    $response->header(rtrim($responseHeaderType, '.'), GeneralUtility::callUserFunction($value['userFunc'], $value, $this));
                }
            }
        }

        $responsePointer = $response;
        $responseString = (string)$response;
        $this->logResponse('response: ' . $response->status(), array('response' => '' . $responseString));
        echo $responseString;
        return $success;
    }

    /**
     * Print the greeting
     *
     * @return boolean Returns if the request has been successfully dispatched
     */
    public function greet() {
        /** @var \Cundd\Rest\Request $request */
        $request = $this->requestFactory->getRequest();

        $this->app->path('/', function ($request) {
            $greeting = 'What\'s up?';
            $hour = date('H');
            if ($hour <= '10') {
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
     *
     * @param \Exception $exception
     * @return Response
     */
    public function exceptionToResponse($exception) {
        if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
            return $this->responseFactory->createErrorResponse('Sorry! Something is wrong. Exception code: ' . $exception->getCode(), 501);
        }
        return $this->responseFactory->createErrorResponse('Sorry! Something is wrong. Exception code: ' . $exception, 501);
    }

    /**
     * Returns the request
     *
     * Better use the RequestFactory::getRequest() instead
     *
     * @return \Cundd\Rest\Request
     */
    public function getRequest() {
        return $this->requestFactory->getRequest();
    }

    /**
     * Returns the Bullet App
     *
     * Use one of the register*() methods if applicable
     *
     * @return \Bullet\App
     */
    public function getApp() {
        return $this->app;
    }

    /**
     * Register the callback for the given parameter
     *
     * @param string $param
     * @param \Closure $callback
     * @return $this
     */
    public function registerParameter($param, \Closure $callback) {
        $this->app->param($param, $callback);
        return $this;
    }

    /**
     * Register the callback for the given path segment
     *
     * @param string $path
     * @param \Closure $callback
     * @return $this
     */
    public function registerPath($path, \Closure $callback) {
        $this->app->path($path, $callback);
        return $this;
    }

    /**
     * Handle GET method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerGetMethod(\Closure $callback) {
        $this->app->method('GET', $callback);
        return $this;
    }

    /**
     * Handle POST method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerPostMethod(\Closure $callback) {
        $this->app->method('POST', $callback);
        return $this;
    }

    /**
     * Handle PUT method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerPutMethod(\Closure $callback) {
        $this->app->method('PUT', $callback);
        return $this;
    }

    /**
     * Handle DELETE method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerDeleteMethod(\Closure $callback) {
        $this->app->method('DELETE', $callback);
        return $this;
    }

    /**
     * Handle PATCH method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerPatchMethod(\Closure $callback) {
        $this->app->method('PATCH', $callback);
        return $this;
    }

    /**
     * Register the callback for the given HTTP method(s)
     *
     * @param string ]string[] $method
     * @param \Closure $callback
     * @return $this
     */
    public function registerHttpMethod($methods, \Closure $callback) {
        $this->app->method($methods, $callback);
        return $this;
    }

    /**
     * Returns the logger
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    public function getLogger() {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }
        return $this->logger;
    }

    /**
     * Logs the given request message and data
     *
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
     *
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
     *
     * @param \Exception $exception
     */
    public function logException($exception) {
        $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
        $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
    }

    /**
     * Logs the given message and data
     *
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
     * Returns a response with the given message and status code
     *
     * @param string|array $data
     * @param int $status
     * @return Response
     * @deprecated use ResponseFactory->createErrorResponse()
     */
    public function createErrorResponse($data, $status) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        return $this->responseFactory->createErrorResponse($data, $status);
    }

    /**
     * Returns a response with the given message and status code
     *
     * @param string|array $data
     * @param int $status
     * @return Response
     * @deprecated use ResponseFactory->createSuccessResponse()
     */
    public function createSuccessResponse($data, $status) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        return $this->responseFactory->createSuccessResponse($data, $status);
    }

    /**
     * Returns a response with the given message and status code
     *
     * @param string|array $data Data to send
     * @param int $status Status code of the response
     * @param bool $forceError If TRUE the response will be treated as an error, otherwise any status below 400 will be a normal response
     * @return Response
     * @deprecated use ResponseFactory->createSuccessResponse() or ResponseFactory->createErrorResponse()
     */
    protected function _createResponse($data, $status, $forceError = FALSE) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        /** @var ResponseFactory $responseFactory */
        $responseFactory = $this->responseFactory;
        return $responseFactory->createResponse($data, $status, $forceError);
    }

    /**
     * @return string
     * @deprecated use getRequest()->path() instead
     */
    public function getPath() {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        return $this->requestFactory->getRequest()->path();
    }

    /**
     * @return string
     * @deprecated use getRequest()->originalPath() instead
     */
    public function getOriginalPath() {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        return $this->requestFactory->getRequest()->originalPath();
    }

    /**
     * Returns the URI
     *
     * @param string $format Reference to be filled with the request format
     * @return string
     * @deprecated use the RequestFactory::getUri() instead
     */
    public function getUri(&$format = '') {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        return $this->requestFactory->getUri($format);
    }

    /**
     * Returns the sent data
     *
     * @return mixed
     * @deprecated use the request's getSentData()
     */
    public function getSentData() {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        return $this->requestFactory->getRequest()->getSentData();
    }

    /**
     * Returns the key to use for the root object if addRootObjectForCollection
     * is enabled
     *
     * @return string
     * @deprecated use the request's getRootObjectKey()
     */
    public function getRootObjectKey() {
        \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
        return $this->requestFactory->getRequest()->getRootObjectKey();
    }

    /**
     * Returns the extension configuration for the given key
     *
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
     * Register singulars to the plural
     */
    protected function registerSingularToPlural() {
        $singularToPlural = $this->objectManager->getConfigurationProvider()->getSetting('singularToPlural');
        if ($singularToPlural) {
            foreach ($singularToPlural as $singular => $plural) {
                Utility::registerSingularForPlural($singular, $plural);
            }
        }
    }

    /**
     * Returns the shared dispatcher instance
     *
     * @return \Cundd\Rest\Dispatcher
     */
    static public function getSharedDispatcher() {
        if (!self::$sharedDispatcher) {
            new static();
        }
        return self::$sharedDispatcher;
    }
}
