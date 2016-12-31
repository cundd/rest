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

use Cundd\Rest\Access\AccessControllerInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher\ApiConfigurationInterface;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Main dispatcher of REST requests
 *
 * The dispatcher will first check the access to the requested resource. Then it will check the cache for a stored
 * response for the current request. If no cached response was found,
 */
class Dispatcher implements SingletonInterface, ApiConfigurationInterface, DispatcherInterface
{
    /**
     * @var ObjectManager
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The shared instance
     *
     * @var \Cundd\Rest\Dispatcher
     */
    protected static $sharedDispatcher;

    /**
     * Initialize
     *
     * @param ObjectManager $objectManager
     * @param bool          $performBootstrap
     */
    public function __construct(ObjectManager $objectManager = null, $performBootstrap = true)
    {
        if ($performBootstrap) {
            (new Bootstrap())->init();
        }

        // Workaround until custom router is implemented
        if (class_exists(\Bullet\App::class)) {
            $this->app = new \Bullet\App();
        }

        $this->objectManager = $objectManager ?: GeneralUtility::makeInstance('Cundd\\Rest\\ObjectManager');
        $this->requestFactory = $this->objectManager->getRequestFactory();
        $this->responseFactory = $this->objectManager->getResponseFactory();
        $this->registerSingularToPlural();

        self::$sharedDispatcher = $this;
    }

    /**
     * Process the raw request
     *
     * Entry point for the PSR 7 middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->requestFactory->registerCurrentRequest($request);
        $this->objectManager->reassignRequest();

        return $this->dispatch($this->requestFactory->getRequest(), $response);
    }

    /**
     * Dispatch the REST request
     *
     * @param RestRequestInterface $request
     * @param ResponseInterface    $response
     * @return ResponseInterface
     */
    public function dispatch(RestRequestInterface $request, ResponseInterface $response)
    {
        $requestPath = $request->getPath();
        if (!$requestPath) {
            return $this->greet();
        }

        // Checks if the request needs authentication
        switch ($this->objectManager->getAccessController()->getAccess($request)) {
            case AccessControllerInterface::ACCESS_ALLOW:
                break;

            case AccessControllerInterface::ACCESS_UNAUTHORIZED:
                return $this->responseFactory->createErrorResponse('Unauthorized', 401);

            case AccessControllerInterface::ACCESS_DENY:
            default:
                return $this->responseFactory->createErrorResponse('Forbidden', 403);
        }

        $newResponse = $this->addAdditionalHeaders($this->getCachedResponseOrCallHandler($request, $response));

        $this->logResponse(
            'response: ' . $newResponse->getStatusCode(),
            array('response' => (string)$newResponse->getBody())
        );

        return $newResponse;
    }

    /**
     * Checks the cache for an entry for the current request and returns it, or calls the handler if nothing is found
     *
     * @param RestRequestInterface $request
     * @param ResponseInterface    $response
     * @return ResponseInterface
     */
    private function getCachedResponseOrCallHandler(RestRequestInterface $request, ResponseInterface $response)
    {
        $cache = $this->objectManager->getCache();
        $cachedResponse = $cache->getCachedValueForRequest($request);

        // If a cached response exists return it
        if ($cachedResponse) {
            return $cachedResponse;
        }

        // If no cached response exists
        $newResponse = $this->callHandler($request);

        // Cache the response
        $cache->setCachedValueForRequest($request, $newResponse);

        return $newResponse;
    }

    /**
     * Call the handler for the current request
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    private function callHandler(RestRequestInterface $request)
    {
        $requestPath = $request->getPath();
        // If a path is given let the handler build up the routes
        $this->logRequest(sprintf('path: "%s" method: "%s"', $requestPath, $request->getMethod()));

        $this->objectManager->getHandler()->configureApiPaths();


        // Let Bullet PHP do the hard work
        $newResponse = $this->app->run($request->getMethod());
        if (class_exists(\Bullet\Response::class) && $newResponse instanceof \Bullet\Response) {
            // Handle exceptions
            if ($newResponse->content() instanceof \Exception) {
                $exception = $newResponse->content();
                $this->logException($exception);

                return $this->exceptionToResponse($exception);
            }

            return $this->responseFactory->createResponse($newResponse->content(), 200)
                ->withHeader(
                    Header::CONTENT_TYPE,
                    $newResponse->contentType() . "; charset=" . $newResponse->encoding()
                );
        }

        /** @var ResponseInterface $newResponse */
        return $newResponse;
    }

    /**
     * Print the greeting
     *
     * @return ResponseInterface
     */
    public function greet()
    {
        $greeting = 'What\'s up?';
        $hour = date('H');
        if ($hour <= '10') {
            $greeting = 'Good Morning!';
        } elseif ($hour >= '23') {
            $greeting = 'Hy! Still awake?';
        }

        $response = $this->responseFactory->createSuccessResponse($greeting, 200);

        $this->logResponse(
            'response: ' . $response->getStatusCode(),
            array('response' => (string)$response->getBody())
        );

        return $response;
    }

    /**
     * Catch and report the exception, that occurred during the request
     *
     * @param \Exception $exception
     * @return ResponseInterface
     */
    public function exceptionToResponse($exception)
    {
        $clientAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if ($clientAddress === '127.0.0.1' || $clientAddress === '::1') {
            $exceptionDetails = $this->getDebugDetails($exception);
        } else {
            $exceptionDetails = sprintf('Sorry! Something is wrong. Exception code #%d', $exception->getCode());
        }

        return $this->responseFactory->createErrorResponse($exceptionDetails, 501);
    }

    /**
     * Returns the request
     *
     * Better use the RequestFactory::getRequest() instead
     *
     * @return RestRequestInterface
     * @deprecated
     */
    public function getRequest()
    {
        return $this->requestFactory->getRequest();
    }

    /**
     * Returns the Bullet App
     *
     * Use one of the register*() methods if applicable
     *
     * @return \Bullet\App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Register the callback for the given parameter
     *
     * @param string   $param
     * @param \Closure $callback
     * @return $this
     */
    public function registerParameter($param, \Closure $callback)
    {
        $this->app->param($param, $callback);

        return $this;
    }

    /**
     * Register the callback for the given path segment
     *
     * @param string   $path
     * @param \Closure $callback
     * @return $this
     */
    public function registerPath($path, \Closure $callback)
    {
        $this->app->path($path, $callback);

        return $this;
    }

    /**
     * Handle GET method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerGetMethod(\Closure $callback)
    {
        $this->app->method('GET', $callback);

        return $this;
    }

    /**
     * Handle POST method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerPostMethod(\Closure $callback)
    {
        $this->app->method('POST', $callback);

        return $this;
    }

    /**
     * Handle PUT method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerPutMethod(\Closure $callback)
    {
        $this->app->method('PUT', $callback);

        return $this;
    }

    /**
     * Handle DELETE method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerDeleteMethod(\Closure $callback)
    {
        $this->app->method('DELETE', $callback);

        return $this;
    }

    /**
     * Handle PATCH method
     *
     * @param \Closure $callback
     * @return $this
     */
    public function registerPatchMethod(\Closure $callback)
    {
        $this->app->method('PATCH', $callback);

        return $this;
    }

    /**
     * Register the callback for the given HTTP method(s)
     *
     * @param          string ]string[] $method
     * @param \Closure $callback
     * @return $this
     */
    public function registerHttpMethod($methods, \Closure $callback)
    {
        $this->app->method($methods, $callback);

        return $this;
    }

    /**
     * Returns the logger
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * Logs the given request message and data
     *
     * @param string $message
     * @param array  $data
     */
    public function logRequest($message, $data = null)
    {
        if ($this->getExtensionConfiguration('logRequests')) {
            $this->log($message, $data);
        }
    }

    /**
     * Logs the given response message and data
     *
     * @param string $message
     * @param array  $data
     */
    public function logResponse($message, $data = null)
    {
        if ($this->getExtensionConfiguration('logResponse')) {
            $this->log($message, $data);
        }
    }

    /**
     * Logs the given exception
     *
     * @param \Exception $exception
     */
    public function logException($exception)
    {
        $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
        $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
    }

    /**
     * Logs the given message and data
     *
     * @param string $message
     * @param array  $data
     */
    public function log($message, $data = null)
    {
        if ($data) {
            $this->getLogger()->log(LogLevel::DEBUG, $message, $data);
        } else {
            $this->getLogger()->log(LogLevel::DEBUG, $message);
        }
    }

    /**
     * Returns the extension configuration for the given key
     *
     * @param $key
     * @return mixed
     */
    protected function getExtensionConfiguration($key)
    {
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

        return isset($configuration[$key]) ? $configuration[$key] : null;
    }

    /**
     * Register singulars to the plural
     */
    protected function registerSingularToPlural()
    {
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
    public static function getSharedDispatcher()
    {
        if (!self::$sharedDispatcher) {
            new static();
        }

        return self::$sharedDispatcher;
    }

    /**
     * Add additional custom response headers
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function addAdditionalHeaders(ResponseInterface $response)
    {
        $additionalResponseHeaders = $this->objectManager
            ->getConfigurationProvider()
            ->getSetting('responseHeaders', null);
        if (is_array($additionalResponseHeaders)) {
            foreach ($additionalResponseHeaders as $responseHeaderType => $value) {
                if (is_string($value)) {
                    $response = $response->withAddedHeader(
                        $responseHeaderType,
                        $value
                    );
                } elseif (is_array($value) && array_key_exists('userFunc', $value)) {
                    $response = $response->withAddedHeader(
                        rtrim($responseHeaderType, '.'),
                        GeneralUtility::callUserFunction($value['userFunc'], $value, $this)
                    );
                }
            }
        }

        return $response;
    }

    /**
     * @param $exception
     * @return array
     */
    private function getDebugTrace(\Exception $exception)
    {
        return array_map(
            function ($step) {
                $arguments = count($step['args']) > 0 ? sprintf('(%d Arguments)', count($step['args'])) : '()';
                if (isset($step['class'])) {
                    return $step['class'] . $step['type'] . $step['function'] . $arguments;
                }
                if (isset($step['function'])) {

                    return $step['function'] . $arguments;
                }

                return '';
            },
            $exception->getTrace()
        );
    }

    /**
     * @param $exception
     * @return array
     */
    private function getDebugDetails(\Exception $exception)
    {
        return [
            'error' => sprintf(
                '%s #%d: %s',
                get_class($exception),
                $exception->getCode(),
                $exception->getMessage()
            ),
            'trace' => $this->getDebugTrace($exception),
        ];
    }
}
