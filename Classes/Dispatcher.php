<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Dispatcher\AfterRequestDispatchedEvent;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Dispatcher\ResponseHeaderUpdaterInterface;
use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Exception\InvalidResourceTypeException;
use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\Router\ResultConverter;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Utility\DebugUtility;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

/**
 * Main dispatcher of REST requests
 *
 * The dispatcher will first check the access to the requested resource.
 * Then it will check the cache for a stored response for the current request.
 * If no cached response was found, the request will be processed by the `router`
 *
 * @phpstan-import-type HeaderValue from ResponseHeaderUpdaterInterface
 */
class Dispatcher implements SingletonInterface, DispatcherInterface
{
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        protected readonly RequestFactoryInterface $requestFactory,
        protected readonly ResponseFactoryInterface $responseFactory,
        protected readonly LoggerInterface $logger,
        protected readonly RouterInterface $router,
        protected readonly ResponseHeaderUpdaterInterface $responseHeaderUpdater,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Process the raw request
     *
     * Entry point for the PSR 7 middleware
     *
     * @throws Exception
     */
    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($this->requestFactory->buildRequest($request));
    }

    /**
     * Dispatch the REST request
     */
    public function dispatch(RestRequestInterface $request): ResponseInterface
    {
        $response = $this->dispatchInternal($request);

        $response = $this->addCorsHeaders(
            $request,
            $this->addAdditionalHeaders(
                $request,
                $this->addDebugHeaders($request, $response)
            )
        );

        $event = new AfterRequestDispatchedEvent($request, $response);
        $this->eventDispatcher->dispatch($event);

        return $event->getResponse();
    }

    /**
     * Checks the cache for an entry for the current request and returns it, or
     * calls the handler if nothing is found
     */
    private function getCachedResponseOrCallHandler(
        RestRequestInterface $request,
    ): ResponseInterface {
        $cache = $this->objectManager->getCache(
            $request,
            $request->getResourceType()
        );
        $cachedResponse = $cache->getCachedValueForRequest($request);

        // If a cached response exists return it
        if ($cachedResponse) {
            return $cachedResponse;
        }

        // If no cached response exists
        $newResponse = $this->callHandler($request);

        // Cache the response
        $resourceConfiguration = $this->objectManager
            ->getConfigurationProvider($request)
            ->getResourceConfiguration($request->getResourceType());
        assert(null !== $resourceConfiguration);
        $cache->setCachedValueForRequest(
            $request,
            $newResponse,
            $resourceConfiguration
        );

        return $newResponse;
    }

    private function getResultConverter(): ResultConverter
    {
        return new ResultConverter(
            $this->router,
            $this->responseFactory,
            $this->logger->logException(...)
        );
    }

    /**
     * Call the handler for the current request
     */
    private function callHandler(RestRequestInterface $request): ResponseInterface
    {
        $requestPath = $request->getPath();

        $resultConverter = $this->getResultConverter();
        $this->logger->logRequest(sprintf(
            'path: "%s" method: "%s"',
            $requestPath,
            $request->getMethod()
        ));

        // If a path is given let the handler build up the routes
        $this->objectManager->getHandler($request)
            ->configureRoutes($resultConverter, $request);

        ErrorHandler::registerHandler();

        return $resultConverter->dispatch($request);
    }

    /**
     * Add additional custom response headers
     */
    private function addAdditionalHeaders(
        RestRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $configurationProvider = $this->objectManager
            ->getConfigurationProvider($request);

        $defaultResponseHeaders = $configurationProvider->getSetting(
            'defaultResponseHeaders',
            []
        );
        InvalidArgumentException::assertAssociativeArray(
            $defaultResponseHeaders,
            'defaultResponseHeaders'
        );

        $response = $this->addHeaders($response, $defaultResponseHeaders, false);

        $fixedResponseHeaders = $configurationProvider->getSetting(
            'responseHeaders',
            []
        );
        InvalidArgumentException::assertAssociativeArray(
            $fixedResponseHeaders,
            'fixedResponseHeaders'
        );

        $response = $this->addHeaders($response, $fixedResponseHeaders, true);

        return $response;
    }

    private function addCorsHeaders(
        RestRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $origin = $request->getHeaderLine('origin');
        if ($origin) {
            $allowedOrigins = $this->objectManager
                ->getConfigurationProvider($request)
                ->getSetting('cors.allowedOrigins', []);
            InvalidArgumentException::assertStringArray(
                $allowedOrigins,
                'allowedOrigins'
            );

            foreach ($allowedOrigins as $allowedOrigin) {
                if ($allowedOrigin === $origin) {
                    return $response->withHeader(Header::CORS_ORIGIN, $allowedOrigin);
                }
            }
        }

        return $response;
    }

    private function dispatchInternal(RestRequestInterface $request): ResponseInterface
    {
        $requestPath = $request->getPath();
        if (!$requestPath || '/' === $requestPath) {
            $request = $request->withResourceType(new ResourceType('greeting'));
        }

        // Checks if the request needs authentication
        $access = $this->objectManager->getAccessController($request)
            ->getAccess($request);

        // TODO: Dispatch event to modify access?

        return match ($access) {
            Access::Allowed, Access::Authorized => (function () use ($request) {
                $newResponse = $this->getCachedResponseOrCallHandler($request);

                $this->logger->logResponse(
                    'response: ' . $newResponse->getStatusCode(),
                    ['response' => (string) $newResponse->getBody()]
                );

                return $newResponse;
            })(),

            Access::Denied => $this->responseFactory
                ->createErrorResponse('Forbidden', 403, $request),
            Access::Unauthorized => $this->responseFactory
                ->createErrorResponse('Unauthorized', 401, $request),

            Access::RequireLogin => throw new UnexpectedValueException(
                'Access::RequireLogin has not been evaluated'
            ),
        };
    }

    /**
     * @param array<string,mixed> $headers
     */
    private function addHeaders(
        ResponseInterface $response,
        array $headers,
        bool $overwrite,
    ): ResponseInterface {
        /** @var HeaderValue[] $typedHeaders */
        $typedHeaders = $headers;

        return $this->responseHeaderUpdater->addHeaders(
            $response,
            $typedHeaders,
            $overwrite
        );
    }

    private function addDebugHeaders(
        RestRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        if (!DebugUtility::allowDebugInformation()) {
            return $response;
        }

        try {
            $resourceConfiguration = $this->objectManager->getConfigurationProvider($request)
                ->getResourceConfiguration($request->getResourceType());
        } catch (InvalidResourceTypeException $exception) {
            return $response;
        }

        return $response
            ->withAddedHeader(
                Header::CUNDD_REST_RESOURCE_TYPE,
                (string) $request->getResourceType()
            )
            ->withAddedHeader(
                Header::CUNDD_REST_PATH,
                (string) $request->getPath()
            )
            ->withAddedHeader(
                Header::CUNDD_REST_HANDLER,
                $resourceConfiguration?->getHandlerClass() ?? ''
            )
            ->withAddedHeader(
                Header::CUNDD_REST_DATA_PROVIDER,
                $resourceConfiguration?->getDataProviderClass() ?? ''
            )
            ->withAddedHeader(
                Header::CUNDD_REST_ALIASES,
                $resourceConfiguration?->getAliases() ?? ''
            );
    }
}
