<?php

namespace Cundd\Rest;

use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\Router\ResultConverter;
use Cundd\Rest\Router\RouterInterface;
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
class Dispatcher implements SingletonInterface, DispatcherInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The shared instance
     *
     * @var Dispatcher
     */
    protected static $sharedDispatcher;

    /**
     * Initialize
     *
     * @param ObjectManagerInterface   $objectManager
     * @param RequestFactoryInterface  $requestFactory
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;

        self::$sharedDispatcher = $this;
    }

    /**
     * Process the raw request
     *
     * Entry point for the PSR 7 middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response Prepared response @deprecated will be removed in 4.0.0
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response = null)
    {
        $this->requestFactory->registerCurrentRequest($request);
        if (method_exists($this->objectManager, 'reassignRequest')) {
            $this->objectManager->reassignRequest();
        }

        return $this->dispatch($this->requestFactory->getRequest(), $response);
    }

    /**
     * Dispatch the REST request
     *
     * @param RestRequestInterface $request
     * @param ResponseInterface    $response Prepared response @deprecated will be removed in 4.0.0
     * @return ResponseInterface
     */
    public function dispatch(RestRequestInterface $request, ResponseInterface $response = null)
    {
        $requestPath = $request->getPath();
        if (!$requestPath || $requestPath === '/') {
            return $this->greet($request);
        }

        // Checks if the request needs authentication
        $access = $this->objectManager->getAccessController()->getAccess($request);
        switch (true) {
            case $access->isAllowed():
            case $access->isAuthorized():
                break;

            case $access->isUnauthorized():
                return $this->responseFactory->createErrorResponse('Unauthorized', 401, $request);

            case $access->isDenied():
            default:
                return $this->responseFactory->createErrorResponse('Forbidden', 403, $request);
        }

        $newResponse = $this->getCachedResponseOrCallHandler($request, $response);
        $newResponse = $this->addAdditionalHeaders($newResponse);

        $this->logger->logResponse(
            'response: ' . $newResponse->getStatusCode(),
            ['response' => (string)$newResponse->getBody()]
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
        $cache = $this->objectManager->getCache($request->getResourceType());
        $cachedResponse = $cache->getCachedValueForRequest($request);

        // If a cached response exists return it
        if ($cachedResponse) {
            return $cachedResponse;
        }

        // If no cached response exists
        $newResponse = $this->callHandler($request);

        // Cache the response
        $resourceConfiguration = $this->objectManager->getConfigurationProvider()
            ->getResourceConfiguration($request->getResourceType());
        $cache->setCachedValueForRequest($request, $newResponse, $resourceConfiguration);

        return $newResponse;
    }

    /**
     * @return ResultConverter
     */
    private function getResultConverter()
    {
        $router = $this->objectManager->get(RouterInterface::class);

        return new ResultConverter($router, $this->responseFactory, [$this, 'logException']);
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

        /** @var RouterInterface $resultConverter */
        $resultConverter = $this->getResultConverter();
        $this->logger->logRequest(sprintf('path: "%s" method: "%s"', $requestPath, $request->getMethod()));

        // If a path is given let the handler build up the routes
        $this->objectManager->getHandler()->configureRoutes($resultConverter, $request);

        ErrorHandler::registerHandler();

        return $resultConverter->dispatch($request);
    }

    /**
     * Print the greeting
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function greet(RestRequestInterface $request)
    {
        $greeting = 'What\'s up?';
        $hour = date('H');
        if ($hour <= '10') {
            $greeting = 'Good Morning!';
        } elseif ($hour >= '23') {
            $greeting = 'Hy! Still awake?';
        }

        $response = $this->responseFactory->createSuccessResponse($greeting, 200, $request);

        $this->logger->logResponse(
            'response: ' . $response->getStatusCode(),
            ['response' => (string)$response->getBody()]
        );

        return $response;
    }

    /**
     * Returns the request
     *
     * Better use the RequestFactory::getRequest() instead
     *
     * @return RestRequestInterface
     * @deprecated will be removed in 4.0.0
     */
    public function getRequest()
    {
        return $this->requestFactory->getRequest();
    }

    /**
     * Returns the logger
     *
     * @return LoggerInterface
     * @deprecated will be removed in 4.0.0
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Logs the given request message and data
     *
     * @param string $message
     * @param array  $data
     * @deprecated will be removed in 4.0.0
     */
    public function logRequest($message, $data = null)
    {
        $this->getLogger()->logRequest($message, $data);
    }

    /**
     * Logs the given response message and data
     *
     * @param string $message
     * @param array  $data
     * @deprecated will be removed in 4.0.0
     */
    public function logResponse($message, $data = null)
    {
        $this->getLogger()->logResponse($message, $data);
    }

    /**
     * Logs the given exception
     *
     * @param \Exception $exception
     * @deprecated will be removed in 4.0.0
     */
    public function logException($exception)
    {
        $this->getLogger()->logException($exception);
    }

    /**
     * Logs the given message and data
     *
     * @param string $message
     * @param array  $data
     * @deprecated will be removed in 4.0.0
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
     * @deprecated will be removed in 4.0.0
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
     * Returns the shared dispatcher instance
     *
     * @return Dispatcher
     */
    public static function getSharedDispatcher()
    {
        if (!self::$sharedDispatcher) {
            /** @var ObjectManagerInterface $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $requestFactory = $objectManager->getRequestFactory();
            $responseFactory = $objectManager->getResponseFactory();
            /** @var LoggerInterface $logger */
            $logger = $objectManager->get(LoggerInterface::class);
            new static($objectManager, $requestFactory, $responseFactory, $logger);
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

        if (!is_array($additionalResponseHeaders)) {
            return $response;
        }

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

        return $response;
    }
}
