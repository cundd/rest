<?php

declare(strict_types=1);

namespace Cundd\Rest\Dispatcher;

use Cundd\Rest\ObjectManagerInterface;

final readonly class DispatcherFactory
{
    public function __construct(private ObjectManagerInterface $objectManager)
    {
    }

    public function build(): DispatcherInterface
    {
        return $this->objectManager->get(DispatcherInterface::class);
    }
}
