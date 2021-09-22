<?php
declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Dispatcher\AfterRequestDispatchedEvent;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Exception\InvalidResourceTypeException;
use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\Router\ResultConverter;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Utility\DebugUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Initialize
     *
     * @param ObjectManagerInterface   $objectManager
     * @param RequestFactoryInterface  $requestFactory
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->objectManager = $objectManager;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher ?? GeneralUtility::getContainer()->get(EventDispatcherInterface::class);

        self::$sharedDispatcher = $this;
    }

    /**
     * Process the raw request
     *
     * Entry point for the PSR 7 middleware
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->requestFactory->registerCurrentRequest($request);

        return $this->dispatch($this->requestFactory->buildRequest($request));
    }

    /**
     * Dispatch the REST request
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RestRequestInterface $request): ResponseInterface
    {
        $response = $this->dispatchInternal($request);

        $response = $this->addCorsHeaders(
            $request,
            $this->addAdditionalHeaders($this->addDebugHeaders($request, $response))
        );

        $event = new AfterRequestDispatchedEvent($request, $response);
        $this->eventDispatcher->dispatch($event);

        return $event->getResponse();
    }

    /**
     * Checks the cache for an entry for the current request and returns it, or calls the handler if nothing is found
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    private function getCachedResponseOrCallHandler(RestRequestInterface $request): ResponseInterface
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
    private function getResultConverter(): ResultConverter
    {
        $router = $this->objectManager->get(RouterInterface::class);

        return new ResultConverter($router, $this->responseFactory, [$this->logger, 'logException']);
    }

    /**
     * Call the handler for the current request
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    private function callHandler(RestRequestInterface $request): ResponseInterface
    {
        $requestPath = $request->getPath();

        /** @var RouterInterface $resultConverter */
        $resultConverter = $this->getResultConverter();
        $this->logger->logRequest(sprintf('path: "%s" method: "%s"', $requestPath, $request->getMethod()));

        // If a path is given let the handler build up the routes
        $this->objectManager->getHandler($request)->configureRoutes($resultConverter, $request);

        ErrorHandler::registerHandler();

        return $resultConverter->dispatch($request);
    }

    /**
     * Print the greeting
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     * @deprecated will be removed in 6.0
     */
    public function greet(RestRequestInterface $request): ResponseInterface
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
     * Returns the shared dispatcher instance
     *
     * @return Dispatcher
     * @deprecated utilize dependency injection instead. Will be removed in 6.0
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
    private function addAdditionalHeaders(ResponseInterface $response): ResponseInterface
    {
        $configurationProvider = $this->objectManager->getConfigurationProvider();
        $fixedResponseHeaders = $configurationProvider->getSetting('responseHeaders', null);
        $defaultResponseHeaders = $configurationProvider->getSetting('defaultResponseHeaders', null);

        $response = $this->addHeaders($response, $defaultResponseHeaders, false);
        $response = $this->addHeaders($response, $fixedResponseHeaders, true);

        return $response;
    }

    private function addCorsHeaders(RestRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $origin = $request->getHeaderLine('origin');
        if ($origin) {
            $allowedOrigins = $this->objectManager
                ->getConfigurationProvider()
                ->getSetting('cors.allowedOrigins', []);

            foreach ($allowedOrigins as $allowedOrigin) {
                if ($allowedOrigin === $origin) {
                    return $response->withHeader(Header::CORS_ORIGIN, $allowedOrigin);
                }
            }
        }

        return $response;
    }

    /**
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    private function dispatchInternal(RestRequestInterface $request): ResponseInterface
    {
        $requestPath = $request->getPath();
        if (!$requestPath || $requestPath === '/') {
            $request = $request->withResourceType(new ResourceType('greeting'));
        }

        // Checks if the request needs authentication
        $access = $this->objectManager->getAccessController($request)->getAccess($request);
        switch (true) {
            case $access->isAllowed():
            case $access->isAuthorized():
                $newResponse = $this->getCachedResponseOrCallHandler($request);

                $this->logger->logResponse(
                    'response: ' . $newResponse->getStatusCode(),
                    ['response' => (string)$newResponse->getBody()]
                );

                return $newResponse;

            case $access->isUnauthorized():
                return $this->responseFactory->createErrorResponse('Unauthorized', 401, $request);

            case $access->isDenied():
            default:
                return $this->responseFactory->createErrorResponse('Forbidden', 403, $request);
        }
    }

    /**
     * @param ResponseInterface $response
     * @param array|null        $defaultResponseHeaders
     * @param bool              $overwrite
     * @return ResponseInterface
     */
    private function addHeaders(
        ResponseInterface $response,
        ?array $defaultResponseHeaders,
        bool $overwrite
    ): ResponseInterface {
        foreach ((array)$defaultResponseHeaders as $responseHeaderType => $value) {
            // If the header is already set skip it unless `$overwrite` is TRUE
            if (!$overwrite && $response->getHeaderLine($responseHeaderType)) {
                continue;
            }

            if (is_string($value)) {
                $response = $response->withHeader(
                    $responseHeaderType,
                    $value
                );
            } elseif (is_array($value) && array_key_exists('userFunc', $value)) {
                $value['response'] = $response;
                $response = $response->withHeader(
                    rtrim($responseHeaderType, '.'),
                    GeneralUtility::callUserFunction($value['userFunc'], $value, $this)
                );
            }
        }

        return $response;
    }

    private function addDebugHeaders(RestRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!DebugUtility::allowDebugInformation()) {
            return $response;
        }

        try {
            $resourceConfiguration = $this->objectManager->getConfigurationProvider()
                ->getResourceConfiguration($request->getResourceType());
        } catch (InvalidResourceTypeException $exception) {
            return $response;
        }

        return $response
            ->withAddedHeader(Header::CUNDD_REST_RESOURCE_TYPE, (string)$request->getResourceType())
            ->withAddedHeader(Header::CUNDD_REST_PATH, (string)$request->getPath())
            ->withAddedHeader(Header::CUNDD_REST_HANDLER, $resourceConfiguration->getHandlerClass())
            ->withAddedHeader(Header::CUNDD_REST_DATA_PROVIDER, $resourceConfiguration->getDataProviderClass())
            ->withAddedHeader(Header::CUNDD_REST_ALIASES, $resourceConfiguration->getAliases());
    }
}
