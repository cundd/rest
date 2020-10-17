<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V9\V9CoreBootstrap;
use Cundd\Rest\Bootstrap\V9\V9LanguageBootstrap;
use Cundd\Rest\ObjectManagerInterface;

class CoreBootstrapFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function build(): CoreBootstrapInterface
    {
        return new V9CoreBootstrap(new V9LanguageBootstrap($this->objectManager), $this->objectManager);
    }
}
