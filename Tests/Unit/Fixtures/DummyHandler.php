<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Fixtures;

use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

class DummyHandler implements HandlerInterface
{
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {

    }
}
