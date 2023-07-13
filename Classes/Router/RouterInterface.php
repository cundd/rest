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
     * @param RestRequestInterface $request
     * @return ResponseInterface|mixed
     */
    public function dispatch(RestRequestInterface $request);

    /**
     * Add the given Route
     *
     * @param RouteInterface $route
     * @return RouterInterface
     */
    public function add(RouteInterface $route): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method GET
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeGet(string|ResourceType $pattern, callable $callback): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePost(string|ResourceType $pattern, callable $callback): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePut(string|ResourceType $pattern, callable $callback): RouterInterface;

    /**
     * Creates and registers a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeDelete(string|ResourceType $pattern, callable $callback): RouterInterface;
}
