<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\Router\Exception\NotFoundException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Router implementation
 */
class Router implements RouterInterface
{
    /**
     * @var array<non-empty-string,RouteInterface[]>
     */
    protected array $registeredRoutes = [
        'GET'  => [],
        'POST' => [],
        'PUT'  => [],
    ];

    /**
     * Dispatch the request
     *
     * @return ResponseInterface|mixed
     */
    public function dispatch(RestRequestInterface $request): mixed
    {
        $route = $this->getMatchingRoute($request);
        if (!$route) {
            return NotFoundException::exceptionWithAlternatives(
                $request->getPath(),
                $request->getMethod(),
                $this->getRoutesForMethod($request)
            );
        }

        $parameters = $this->getPreparedParametersForRoute($request, $route);

        return $route->process($request, ...$parameters);
    }

    /**
     * Add the given Route
     */
    public function add(RouteInterface $route): RouterInterface
    {
        $method = $route->getMethod();
        if (!isset($this->registeredRoutes[$method])) {
            $this->registeredRoutes[$method] = [];
        }

        $this->registeredRoutes[$method][$route->getPattern()] = $route;

        return $this;
    }

    /**
     * Create and register a new Route with the given pattern and callback for the method GET
     *
     * @param non-empty-string|\Cundd\Rest\Request\ResourceType $pattern
     */
    public function routeGet(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface {
        $this->add(Route::get($pattern, $callback));

        return $this;
    }

    /**
     * Create and register a new Route with the given pattern and callback for the method POST
     *
     * @param non-empty-string|ResourceType $pattern
     */
    public function routePost(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface {
        $this->add(Route::post($pattern, $callback));

        return $this;
    }

    /**
     * Create and register a new Route with the given pattern and callback for the method PUT
     *
     * @param non-empty-string|\Cundd\Rest\Request\ResourceType $pattern
     */
    public function routePut(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface {
        $this->add(Route::put($pattern, $callback));

        return $this;
    }

    /**
     * Create and register a new Route with the given pattern and callback for the method DELETE
     *
     * @param non-empty-string|ResourceType $pattern
     */
    public function routeDelete(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface {
        $this->add(Route::delete($pattern, $callback));

        return $this;
    }

    /**
     * @return RouteInterface[]
     */
    public function getMatchingRoutes(RestRequestInterface $request): array
    {
        $registeredRoutes = $this->getRoutesForMethod($request);
        if (empty($registeredRoutes)) {
            return [];
        }

        $path = $request->getPath();
        $matchingRoutes = [];
        foreach ($registeredRoutes as $pattern => $route) {
            $regularExpression = $this->patternToRegularExpression($pattern);
            if (preg_match($regularExpression, $path)) {
                $matchingRoutes[$pattern] = $route;
            }
        }

        return $this->sortRoutesByPriority($matchingRoutes);
    }

    /**
     * Return the prepared parameters
     *
     * @return array<mixed>
     */
    public function getPreparedParameters(RestRequestInterface $request): array
    {
        $route = $this->getMatchingRoute($request);
        if (!$route) {
            return [];
        }

        return $this->getPreparedParametersForRoute($request, $route);
    }

    /**
     * Return the prepared parameters
     *
     * @return array<mixed>
     */
    private function getPreparedParametersForRoute(RestRequestInterface $request, RouteInterface $route): array
    {
        $segments = explode('/', $request->getPath());
        $parameters = [];
        foreach ($route->getParameters() as $index => $type) {
            $parameters[] = $this->getPreparedParameter($type, $segments[$index]);
        }

        return $parameters;
    }

    /**
     * Return the prepared parameter
     */
    private function getPreparedParameter(string $type, string $segment): mixed
    {
        switch ($type) {
            case ParameterTypeInterface::RAW:
            case ParameterTypeInterface::SLUG:
                return (string) $segment;
            case ParameterTypeInterface::BOOLEAN:
                return filter_var($segment, FILTER_VALIDATE_BOOLEAN);
            case ParameterTypeInterface::INTEGER:
                return filter_var($segment, FILTER_VALIDATE_INT);
            case ParameterTypeInterface::FLOAT:
                return filter_var($segment, FILTER_VALIDATE_FLOAT);
            default:
                throw new InvalidArgumentException(sprintf(
                    'Invalid parameter type "%s"',
                    $type
                ));
        }
    }

    private function patternToRegularExpression(string $pattern): string
    {
        $outputPattern = $pattern;
        $parameterTypeToRegex = [
            ParameterTypeInterface::RAW     => '[^/]+',
            ParameterTypeInterface::SLUG    => '[a-zA-Z0-9\._\-]+',
            ParameterTypeInterface::INTEGER => '[0-9]+',
            ParameterTypeInterface::FLOAT   => '[0-9]+\.[0-9]+',
            ParameterTypeInterface::BOOLEAN => '(1|true|on|yes|0|false|off|no)',
        ];

        foreach ($parameterTypeToRegex as $parameterType => $regex) {
            $outputPattern = str_replace('{' . $parameterType . '}', $regex, $outputPattern);
        }

        return '!^' . $outputPattern . '$!';
    }

    private function getMatchingRoute(RestRequestInterface $request): ?RouteInterface
    {
        $matchingRoutes = $this->getMatchingRoutes($request);

        return reset($matchingRoutes) ?: null;
    }

    /**
     * @param RouteInterface[] $matchingRoutes
     *
     * @return RouteInterface[]
     */
    private function sortRoutesByPriority(array $matchingRoutes): array
    {
        uasort(
            $matchingRoutes,
            function (RouteInterface $a, RouteInterface $b): int {
                $priorityA = $a->getPriority();
                $priorityB = $b->getPriority();
                if ($priorityA === $priorityB) {
                    return 0;
                }

                return ($priorityA > $priorityB) ? -1 : 1;
            }
        );

        return $matchingRoutes;
    }

    /**
     * @return RouteInterface[]
     */
    private function getRoutesForMethod(RestRequestInterface $request): array
    {
        return $this->registeredRoutes[$request->getMethod()] ?? [];
    }
}
