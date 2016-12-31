<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.08.15
 * Time: 13:22
 */
namespace Cundd\Rest\Dispatcher;

use Closure;

/**
 * Interface to configure the API paths and methods
 */
interface ApiConfigurationInterface
{
    /**
     * Register the callback for the given parameter
     *
     * @param string $param
     * @param Closure $callback
     * @return $this
     */
    public function registerParameter($param, Closure $callback);

    /**
     * Register the callback for the given path segment
     *
     * @param string $path
     * @param Closure $callback
     * @return $this
     */
    public function registerPath($path, Closure $callback);

    /**
     * Handle GET method
     *
     * @param Closure $callback
     * @return $this
     */
    public function registerGetMethod(Closure $callback);

    /**
     * Handle POST method
     *
     * @param Closure $callback
     * @return $this
     */
    public function registerPostMethod(Closure $callback);

    /**
     * Handle PUT method
     *
     * @param Closure $callback
     * @return $this
     */
    public function registerPutMethod(Closure $callback);

    /**
     * Handle DELETE method
     *
     * @param Closure $callback
     * @return $this
     */
    public function registerDeleteMethod(Closure $callback);

    /**
     * Handle PATCH method
     *
     * @param Closure $callback
     * @return $this
     */
    public function registerPatchMethod(Closure $callback);

    /**
     * Register the callback for the given HTTP method(s)
     *
     * @param string]string[] $method
     * @param \Closure $callback
     * @return $this
     */
    public function registerHttpMethod($methods, \Closure $callback);
}
