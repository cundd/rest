<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Exception\NotFoundException;
use Cundd\Rest\Utility\DebugUtility;
use Exception;
use Psr\Http\Message\ResponseInterface;

use function array_map;
use function implode;
use function preg_replace;
use function str_replace;
use function substr;

/**
 * Class to convert simple Router results into Response instances and handle exceptions
 */
class ResultConverter implements RouterInterface
{
    private RouterInterface $router;
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var callable
     */
    private $exceptionHandler;

    /**
     * @param callable(Exception, RestRequestInterface): void $exceptionHandler
     */
    public function __construct(
        RouterInterface $router,
        ResponseFactoryInterface $responseFactory,
        callable $exceptionHandler,
    ) {
        $this->router = $router;
        $this->responseFactory = $responseFactory;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Dispatch the request to the router and convert the result
     */
    public function dispatch(RestRequestInterface $request): ResponseInterface
    {
        try {
            $result = $this->router->dispatch($request);
        } catch (Exception $exception) {
            $result = $exception;
        }
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if ($result instanceof NotFoundException) {
            return $this->notFoundToResponse($result, $request);
        }
        if ($result instanceof Exception) {
            return $this->exceptionToResponse($result, $request);
        }

        return $this->responseFactory->createSuccessResponse($result, 200, $request);
    }

    /**
     * Add the given Route
     */
    public function add(RouteInterface $route): RouterInterface
    {
        $this->router->add($route);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method GET
     */
    public function routeGet(string|ResourceType $pattern, callable $callback): RouterInterface
    {
        $this->router->routeGet($pattern, $callback);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method POST
     */
    public function routePost(string|ResourceType $pattern, callable $callback): RouterInterface
    {
        $this->router->routePost($pattern, $callback);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method PUT
     */
    public function routePut(string|ResourceType $pattern, callable $callback): RouterInterface
    {
        $this->router->routePut($pattern, $callback);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method DELETE
     */
    public function routeDelete(string|ResourceType $pattern, callable $callback): RouterInterface
    {
        $this->router->routeDelete($pattern, $callback);

        return $this;
    }

    /**
     * Convert exceptions that occurred during the dispatching
     */
    private function exceptionToResponse(
        Exception $exception,
        RestRequestInterface $request,
    ): ResponseInterface {
        try {
            $exceptionHandler = $this->exceptionHandler;
            $exceptionHandler($exception, $request);
        } catch (Exception $handlerError) {
        }

        if ($this->getShowDebugInformation()) {
            $exceptionDetails = $this->getDebugDetails($exception);
        } else {
            $exceptionDetails = sprintf(
                'Sorry! Something is wrong. Exception code #%d',
                $exception->getCode()
            );
        }

        return $this->responseFactory->createErrorResponse($exceptionDetails, 501, $request);
    }

    /**
     * @return string[]
     */
    private function getDebugTrace(Exception $exception): array
    {
        return array_map(
            function (array $step): string {
                $arguments = count($step['args'] ?? []) > 0
                    ? sprintf('(%d Arguments)', count($step['args']))
                    : '()';

                if (isset($step['class'])) {
                    return $step['class'] . ($step['type'] ?? '') . $step['function'] . $arguments;
                }

                return $step['function'] . $arguments;
            },
            $exception->getTrace()
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function getDebugDetails(Exception $exception): array
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

    /**
     * Handle cases where no matching Route was found
     *
     * If debugging information is allowed for the client and alternative Route
     * suggestions where provided by the Router, they will be sent as additional
     * header
     */
    private function notFoundToResponse(
        NotFoundException $result,
        RestRequestInterface $request,
    ): ResponseInterface {
        $response = $this->responseFactory->createErrorResponse(
            $result->getMessage() ?: null,
            404,
            $request
        );
        if (!$this->getShowDebugInformation()) {
            return $response;
        }

        if (!$result->getAlternativeRoutes()) {
            return $response->withHeader(Header::CUNDD_REST_ROUTER_ALTERNATIVE_ROUTES, 'no suggestions');
        }

        $alternativePatterns = implode(
            ', ',
            array_map(
                function (RouteInterface $r): string {
                    return $r->getPattern();
                },
                $result->getAlternativeRoutes()
            )
        );

        $alternativePatterns = str_replace(["\r", "\n"], '', $alternativePatterns);
        $alternativePatterns = (string) preg_replace('/[^\x20-\x7e]/', '', $alternativePatterns);

        $maxByteLength = 1024;
        if (strlen($alternativePatterns) > $maxByteLength) {
            $alternativePatterns = substr($alternativePatterns, 0, $maxByteLength - 3) . '...';
        }

        return $response->withHeader(
            Header::CUNDD_REST_ROUTER_ALTERNATIVE_ROUTES,
            $alternativePatterns
        );
    }

    private function getShowDebugInformation(): bool
    {
        return DebugUtility::allowDebugInformation();
    }
}
