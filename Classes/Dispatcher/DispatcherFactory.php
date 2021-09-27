<?php
declare(strict_types=1);

namespace Cundd\Rest\Dispatcher;

use Cundd\Rest\Dispatcher;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatcherFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function build(): DispatcherInterface
    {
        $requestFactory = $this->objectManager->getRequestFactory();
        $responseFactory = $this->objectManager->getResponseFactory();
        $logger = $this->objectManager->get(LoggerInterface::class);
        $eventDispatcher = $this->objectManager->get(EventDispatcherInterface::class);

        return new Dispatcher($this->objectManager, $requestFactory, $responseFactory, $logger, $eventDispatcher);
    }
}
