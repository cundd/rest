<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Request\ResourceType;

/**
 * Interface for Route factory methods
 */
interface RouteFactoryInterface
{
    /**
     * Creates a new Route with the given pattern and callback for the method GET
     */
    public static function get(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method POST
     */
    public static function post(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method PUT
     */
    public static function put(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method DELETE
     */
    public static function delete(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method OPTIONS
     */
    public static function options(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method PATCH
     */
    public static function patch(string|ResourceType $pattern, callable $callback): RouteInterface;
}
