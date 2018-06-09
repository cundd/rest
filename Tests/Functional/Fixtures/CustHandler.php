<?php

namespace Cundd\Rest\Tests\Functional\Fixtures;


use Cundd\Rest\Handler\HandlerDescriptionInterface;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

/**
 * Example Custom Handler
 */
class CustHandler implements HandlerInterface, HandlerDescriptionInterface
{
    /**
     * Return the description of the handler
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Example Custom Handler';
    }

    public function getIndex(RestRequestInterface $request)
    {
        return $request->getMethod() . ' Index';
    }

    public function getFoo(RestRequestInterface $request)
    {
        return $request->getMethod() . ' Foo';
    }

    public function postBar(RestRequestInterface $request)
    {
        return $request->getMethod() . ' Bar';
    }

    /**
     * Let the handler configure the routes
     *
     * @param RouterInterface      $router
     * @param RestRequestInterface $request
     */
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $router->routeGet($request->getResourceType() . '/?', [$this, 'getIndex']);
        $router->routeGet($request->getResourceType() . '/foo/?', [$this, 'getFoo']);
        $router->routePost($request->getResourceType() . '/bar/?', [$this, 'postBar']);
    }
}
