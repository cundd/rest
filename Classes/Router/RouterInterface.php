<?php

namespace Cundd\Rest\Router;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for Routers
 */
interface RouterInterface
{
    /**
     * Dispatch the request
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface|mixed
     */
    public function dispatch(RestRequestInterface $request);

    /**
     * Add the given Route
     *
     * @param Route $route
     * @return RouterInterface
     */
    public function add(Route $route);

    /**
     * Creates and registers a new Route with the given pattern and callback for the method GET
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeGet($pattern, callable $callback);

    /**
     * Creates and registers a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePost($pattern, callable $callback);

    /**
     * Creates and registers a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePut($pattern, callable $callback);

    /**
     * Creates and registers a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeDelete($pattern, callable $callback);
}
