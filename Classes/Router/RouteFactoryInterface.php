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
    public static function get($pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function post($pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function put($pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function delete($pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method OPTIONS
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function options($pattern, callable $callback): RouteInterface;

    /**
     * Creates a new Route with the given pattern and callback for the method PATCH
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouteInterface
     */
    public static function patch($pattern, callable $callback): RouteInterface;
}
