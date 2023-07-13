<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Domain\Model\ResourceType;

/**
 * Interface for Route factory methods
 */
interface RouteFactoryInterface
{
    /**
     * Creates a new Route with the given pattern and callback for the method GET
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function get(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function post(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function put(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function delete(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method OPTIONS
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function options(string|ResourceType $pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method PATCH
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function patch(string|ResourceType $pattern, callable $callback): RouteInterface;
}
