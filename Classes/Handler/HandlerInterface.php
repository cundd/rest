<?php


namespace Cundd\Rest\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

/**
 * Interface for handlers of API requests
 */
interface HandlerInterface
{
    /**
     * Let the handler configure the routes
     *
     * @param RouterInterface      $router
     * @param RestRequestInterface $request
     */
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request);
}
