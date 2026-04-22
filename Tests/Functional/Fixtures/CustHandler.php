<?php

declare(strict_types=1);

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
     */
    public function getDescription(): string
    {
        return 'Example Custom Handler';
    }

    public function getIndex(RestRequestInterface $request): string
    {
        return $request->getMethod() . ' Index';
    }

    public function getFoo(RestRequestInterface $request): string
    {
        return $request->getMethod() . ' Foo';
    }

    public function postBar(RestRequestInterface $request): string
    {
        return $request->getMethod() . ' Bar';
    }

    public function configureRoutes(
        RouterInterface $router,
        RestRequestInterface $request,
    ): void {
        $router->routeGet($request->getResourceType() . '/?', $this->getIndex(...));
        $router->routeGet($request->getResourceType() . '/foo/?', $this->getFoo(...));
        $router->routePost($request->getResourceType() . '/bar/?', $this->postBar(...));
    }
}
