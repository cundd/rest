<?php

declare(strict_types=1);

namespace Cundd\Rest\Dispatcher;

use Cundd\Rest\Dispatcher;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Router\RouterInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatcherFactory
{
    public function __construct(readonly private ObjectManagerInterface $objectManager)
    {
    }

    public function build(): DispatcherInterface
    {
        $requestFactory = $this->objectManager->getRequestFactory();
        $responseFactory = $this->objectManager->getResponseFactory();
        $logger = $this->objectManager->get(LoggerInterface::class);
        $router = $this->objectManager->get(RouterInterface::class);
        $eventDispatcher = $this->objectManager->get(EventDispatcherInterface::class);

        return new Dispatcher(
            $this->objectManager,
            $requestFactory,
            $responseFactory,
            $logger,
            $router,
            $eventDispatcher
        );
    }
}
