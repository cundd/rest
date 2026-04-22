<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\ResourceType;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for Routers
 */
interface RouterInterface
{
    /**
     * Dispatch the request
     *
     * @return ResponseInterface|mixed
     */
    public function dispatch(RestRequestInterface $request): mixed;

    /**
     * Add the given Route
     */
    public function add(RouteInterface $route): RouterInterface;

    /**
     * Create and registers a new Route with the given pattern and callback for
     * the method GET
     */
    public function routeGet(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface;

    /**
     * Create and registers a new Route with the given pattern and callback for
     * the method POST
     */
    public function routePost(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface;

    /**
     * Create and registers a new Route with the given pattern and callback for
     * the method PUT
     */
    public function routePut(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface;

    /**
     * Create and registers a new Route with the given pattern and callback for
     * the method DELETE
     */
    public function routeDelete(
        string|ResourceType $pattern,
        callable $callback,
    ): RouterInterface;
}
