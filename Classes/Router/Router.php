<?php

namespace Cundd\Rest\Router;


use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;

/**
 * Router implementation
 */
class Router implements RouterInterface
{
    protected $registeredRoutes = [
        'GET'  => [],
        'POST' => [],
        'PUT'  => [],
    ];

    /**
     * Dispatch the request
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface|mixed
     */
    public function dispatch(RestRequestInterface $request)
    {
        $parameters = $this->getPreparedParameters($request);
        $route = $this->getMatchingRoute($request);

        if (!$route) {
            return new NotFoundException();
        }

        return $route->process($request, ...$parameters);
    }

    /**
     * Add the given Route
     *
     * @param Route $route
     * @return RouterInterface
     */
    public function add(Route $route)
    {
        $method = $route->getMethod();
        if (!isset($this->registeredRoutes[$method])) {
            $this->registeredRoutes[$method] = [];
        }

        $this->registeredRoutes[$method][$route->getPattern()] = $route;

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method GET
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeGet($pattern, callable $callback)
    {
        $this->add(Route::get($pattern, $callback));

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePost($pattern, callable $callback)
    {
        $this->add(Route::post($pattern, $callback));

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePut($pattern, callable $callback)
    {
        $this->add(Route::put($pattern, $callback));

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeDelete($pattern, callable $callback)
    {
        $this->add(Route::delete($pattern, $callback));

        return $this;
    }

    /**
     * @param RestRequestInterface $request
     * @return Route[]
     */
    public function getMatchingRoutes(RestRequestInterface $request)
    {
        $method = $request->getMethod();
        if (!isset($this->registeredRoutes[$method])) {
            return [];
        }

        $path = $request->getPath();
        $matchingRoutes = [];
        foreach ($this->registeredRoutes[$method] as $pattern => $route) {
            $regularExpression = $this->patternToRegularExpression($pattern);
            if (preg_match($regularExpression, $path)) {
                $matchingRoutes[$pattern] = $route;
            }
        }

        return $this->sortRoutesByPriority($matchingRoutes);
    }

    /**
     * Returns the prepared parameters
     *
     * @param RestRequestInterface $request
     * @return array
     */
    public function getPreparedParameters(RestRequestInterface $request)
    {
        $route = $this->getMatchingRoute($request);
        if (!$route) {
            return [];
        }

        $segments = explode('/', $request->getPath());
        $parameters = [];
        foreach ($route->getParameters() as $index => $type) {
            $parameters[] = $this->getPreparedParameter($type, $segments[$index]);
        }

        return $parameters;
    }

    /**
     * Returns the prepared parameter
     *
     * @param string $type
     * @param string $segment
     * @return mixed
     */
    private function getPreparedParameter($type, $segment)
    {
        switch ($type) {
            case ParameterTypeInterface::SLUG:
                return (string)$segment;
            case ParameterTypeInterface::BOOLEAN:
                return filter_var($segment, FILTER_VALIDATE_BOOLEAN);
            case ParameterTypeInterface::INTEGER:
                return filter_var($segment, FILTER_VALIDATE_INT);
            case ParameterTypeInterface::FLOAT:
                return filter_var($segment, FILTER_VALIDATE_FLOAT);
            default:
                throw new \InvalidArgumentException(sprintf('Invalid parameter type "%s"', $type));
        }
    }

    /**
     * @param string $pattern
     * @return string
     */
    private function patternToRegularExpression($pattern)
    {
        $outputPattern = $pattern;
        $parameterTypeToRegex = [
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

    /**
     * @param RestRequestInterface $request
     * @return Route
     */
    private function getMatchingRoute(RestRequestInterface $request)
    {
        $matchingRoutes = $this->getMatchingRoutes($request);

        return reset($matchingRoutes);
    }

    /**
     * @param $matchingRoutes
     * @return array
     */
    private function sortRoutesByPriority(array $matchingRoutes)
    {
        uasort(
            $matchingRoutes,
            function (Route $a, Route $b) {
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
}
