<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V10\V10LanguageBootstrap;
use Cundd\Rest\Bootstrap\V11\V11LanguageBootstrap;
use Cundd\Rest\Bootstrap\V8\V8LanguageBootstrap;
use Cundd\Rest\Bootstrap\V9\V9LanguageBootstrap;
use Cundd\Rest\ObjectManagerInterface;
use TYPO3\CMS\Core\Information\Typo3Version;

use function class_exists;

class LanguageBootstrapFactory
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

    public function build(): LanguageBootstrapInterface
    {
        if (!class_exists(Typo3Version::class)) {
            return new V8LanguageBootstrap($this->objectManager);
        }

        if ((new Typo3Version())->getMajorVersion() == 9) {
            return new V9LanguageBootstrap();
        } elseif ((new Typo3Version())->getMajorVersion() == 10) {
            return new V10LanguageBootstrap();
        } else {
            return new V11LanguageBootstrap();
        }
    }
}
