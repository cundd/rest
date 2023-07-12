<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V11\V11CoreBootstrap;
use Cundd\Rest\Bootstrap\V12\V12CoreBootstrap;
use Cundd\Rest\ObjectManagerInterface;
use TYPO3\CMS\Core\Information\Typo3Version;

class CoreBootstrapFactory
{
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function build(): CoreBootstrapInterface
    {
        if ((new Typo3Version())->getMajorVersion() == 11) {
            return new V11CoreBootstrap($this->objectManager);
        } else {
            return new V12CoreBootstrap($this->objectManager);
        }
    }
}
