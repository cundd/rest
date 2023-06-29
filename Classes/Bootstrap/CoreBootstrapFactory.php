<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V10\V10CoreBootstrap;
use Cundd\Rest\Bootstrap\V11\V11CoreBootstrap;
use Cundd\Rest\Bootstrap\V8\V8CoreBootstrap;
use Cundd\Rest\Bootstrap\V8\V8LanguageBootstrap;
use Cundd\Rest\Bootstrap\V9\V9CoreBootstrap;
use Cundd\Rest\ObjectManagerInterface;
use TYPO3\CMS\Core\Information\Typo3Version;

use function class_exists;

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
        if (!class_exists(Typo3Version::class)) {
            return new V8CoreBootstrap(new V8LanguageBootstrap($this->objectManager), $this->objectManager);
        }

        if ((new Typo3Version())->getMajorVersion() == 9) {
            return new V9CoreBootstrap($this->objectManager);
        } elseif ((new Typo3Version())->getMajorVersion() == 10) {
            return new V10CoreBootstrap($this->objectManager);
        } else {
            return new V11CoreBootstrap($this->objectManager);
        }
    }
}
