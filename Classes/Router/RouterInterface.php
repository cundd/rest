<?php

declare(strict_types=1);

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
     * @return ResponseInterface|mixed
     */
    public function dispatch(RestRequestInterface $request);

    /**
     * Add the given Route
     */
    public function add(RouteInterface $route): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method GET
     */
    public function routeGet(string|ResourceType $pattern, callable $callback): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method POST
     */
    public function routePost(string|ResourceType $pattern, callable $callback): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method PUT
     */
    public function routePut(string|ResourceType $pattern, callable $callback): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method DELETE
     */
    public function routeDelete(string|ResourceType $pattern, callable $callback): RouterInterface;
}
