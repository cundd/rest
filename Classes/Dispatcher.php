<?php

namespace Cundd\Rest;

use Cundd\Rest\Access\AccessControllerInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Http\RestRequestInterface;
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
     * @var ObjectManager
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
            return $this->greet($request);
        }

        // Checks if the request needs authentication
        switch ($this->objectManager->getAccessController()->getAccess($request)) {
            case AccessControllerInterface::ACCESS_ALLOW:
                break;

            case AccessControllerInterface::ACCESS_UNAUTHORIZED:
                return $this->responseFactory->createErrorResponse('Unauthorized', 401, $request);

            case AccessControllerInterface::ACCESS_DENY:
            default:
                return $this->responseFactory->createErrorResponse('Forbidden', 403, $request);
        }

        $newResponse = $this->addAdditionalHeaders($this->getCachedResponseOrCallHandler($request, $response));

        $this->logResponse(
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
        $this->logRequest(sprintf('path: "%s" method: "%s"', $requestPath, $request->getMethod()));

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

        $this->logResponse(
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
     * @deprecated
     */
    public function getRequest()
    {
        return $this->requestFactory->getRequest();
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
        $this->getLogger()->log(LogLevel::ERROR, $message, ['exception' => $exception]);
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
}
