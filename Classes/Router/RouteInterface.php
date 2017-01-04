<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02.01.17
 * Time: 17:43
 */
namespace Cundd\Rest\Router;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for Routes
 */
interface RouteInterface
{
    /**
     * Returns the normalized path pattern
     *
     * @return string
     */
    public function getPattern();

    /**
     * Returns the request method for this route
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns the requested parameters
     *
     * @return string[]
     */
    public function getParameters();

    /**
     * Returns the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence over paths with parameter expressions.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Process the route
     *
     * @param RestRequestInterface $request
     * @param array                $parameters
     * @return ResponseInterface
     */
    public function process(RestRequestInterface $request, ...$parameters);

    /**
     * Creates a new Route with the given pattern and callback for the method GET
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return static
     */
    public static function get($pattern, callable $callback);

    /**
     * Creates a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return static
     */
    public static function post($pattern, callable $callback);

    /**
     * Creates a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return static
     */
    public static function put($pattern, callable $callback);

    /**
     * Creates a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return static
     */
    public static function delete($pattern, callable $callback);
}
