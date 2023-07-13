<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V12\V12CoreBootstrap;
use Cundd\Rest\ObjectManagerInterface;

class CoreBootstrapFactory
{
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function build(): CoreBootstrapInterface
    {
        return new V12CoreBootstrap($this->objectManager);
    }
}
