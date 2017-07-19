<?php

namespace Cundd\Rest\Tests\Documentation;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\Router;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Cundd\TestFlight\Event\Event;
use Cundd\TestFlight\Event\EventDispatcherInterface;
use Cundd\TestFlight\TestRunner\TestRunnerInterface;
use Prophecy\Prophet;

class Bootstrap
{
    use RequestBuilderTrait;

    public function run(EventDispatcherInterface $eventDispatcher)
    {
        class_alias(Route::class, 'Route');
        $eventDispatcher->register(
            TestRunnerInterface::EVENT_TEST_WILL_RUN,
            function (Event $event) use ($eventDispatcher) {
                $prophet = new Prophet();
                $prophet->prophesize(RestRequestInterface::class);
                $event->getContext()->addVariables(
                    [
                        'router'  => new Router(),
                        'request' => RequestBuilderTrait::buildTestRequest('some/path'),
                    ]
                );
            }
        );
    }
}

/** @var EventDispatcherInterface $eventDispatcher */
(new Bootstrap())->run($eventDispatcher);