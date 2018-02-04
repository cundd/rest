<?php


namespace Cundd\Rest\Documentation\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\Exception\NotFoundException;
use Cundd\Rest\Router\RouteInterface;
use Cundd\Rest\Router\Router;

/**
 * A Router subclass that support fetching of all registered Routes, but will never execute
 */
class DescriptiveRouter extends Router
{
    public function dispatch(RestRequestInterface $request)
    {
        return new NotFoundException();
    }

    /**
     * Returns all registered Routes
     *
     * @return RouteInterface[][]
     */
    public function getRegisteredRoutes()
    {
        return $this->registeredRoutes;
    }
}
