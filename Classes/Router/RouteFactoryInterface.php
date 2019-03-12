<?php
declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Router\Route;

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
     * @return Route
     */
    public static function get($pattern, callable $callback): Route;

    /**
     * Creates a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return \Cundd\Rest\Router\Route
     */
    public static function post($pattern, callable $callback): Route;

    /**
     * Creates a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return \Cundd\Rest\Router\Route
     */
    public static function put($pattern, callable $callback): Route;

    /**
     * Creates a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return \Cundd\Rest\Router\Route
     */
    public static function delete($pattern, callable $callback): Route;

    /**
     * Creates a new Route with the given pattern and callback for the method OPTIONS
     *
     * @param string   $pattern
     * @param callable $callback
     * @return \Cundd\Rest\Router\Route
     */
    public static function options($pattern, callable $callback): Route;

    /**
     * Creates a new Route with the given pattern and callback for the method PATCH
     *
     * @param string   $pattern
     * @param callable $callback
     * @return \Cundd\Rest\Router\Route
     */
    public static function patch($pattern, callable $callback): Route;
}
