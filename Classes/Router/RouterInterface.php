<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 03.01.17
 * Time: 22:48
 */
namespace Cundd\Rest\Router;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @return $this
     */
    public function add(Route $route);
}
