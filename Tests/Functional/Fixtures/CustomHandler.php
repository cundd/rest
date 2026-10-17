<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Fixtures;

use Cundd\Rest\Handler\HandlerDescriptionInterface;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Example Custom Handler
 */
final class CustomHandler implements HandlerInterface, HandlerDescriptionInterface
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
    ) {
    }

    /**
     * Return the description of the handler
     *
     * Implement optional interface `HandlerDescriptionInterface`
     */
    public function getDescription(): string
    {
        return 'Example Custom Handler';
    }

    public function index(RestRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createSuccessResponse(
            $request->getMethod() . ' Index',
            200,
            $request
        );
    }

    public function getFoo(RestRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createSuccessResponse(
            $request->getMethod() . ' Foo',
            200,
            $request
        );
    }

    public function postBar(RestRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createSuccessResponse(
            $request->getMethod() . ' Bar',
            200,
            $request
        );
    }

    public function stringRoute(
        RestRequestInterface $request,
        string $value,
    ): ResponseInterface {
        return $this->responseFactory->createSuccessResponse(
            [
                'type'  => get_debug_type($value),
                'value' => $value,
            ],
            200,
            $request
        );
    }

    public function intRoute(
        RestRequestInterface $request,
        int $value,
    ): ResponseInterface {
        return $this->responseFactory->createSuccessResponse(
            [
                'type'  => get_debug_type($value),
                'value' => $value,
            ],
            200,
            $request
        );
    }

    public function floatRoute(
        RestRequestInterface $request,
        float $value,
    ): ResponseInterface {
        return $this->responseFactory->createSuccessResponse(
            [
                'type'  => get_debug_type($value),
                'value' => $value,
            ],
            200,
            $request
        );
    }

    public function booleanRoute(
        RestRequestInterface $request,
        bool $value,
    ): ResponseInterface {
        return $this->responseFactory->createSuccessResponse(
            [
                'type'  => get_debug_type($value),
                'value' => $value,
            ],
            200,
            $request
        );
    }

    public function configureRoutes(
        RouterInterface $router,
        RestRequestInterface $request,
    ): void {
        $router->routeGet($request->getResourceType() . '/?', $this->index(...));
        $router->routeGet($request->getResourceType() . '/foo/?', $this->getFoo(...));
        $router->routePost($request->getResourceType() . '/bar/?', $this->postBar(...));

        $router->routeGet(
            $request->getResourceType() . '/string-route/{string}/?',
            $this->stringRoute(...)
        );

        $router->routeGet(
            $request->getResourceType() . '/int-route/{int}/?',
            $this->intRoute(...)
        );

        $router->routeGet(
            $request->getResourceType() . '/float-route/{float}/?',
            $this->floatRoute(...)
        );

        $router->routeGet(
            $request->getResourceType() . '/boolean-route/{boolean}/?',
            $this->booleanRoute(...)
        );
    }
}
