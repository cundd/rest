<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V11\V11LanguageBootstrap;
use Cundd\Rest\ObjectManagerInterface;
use TYPO3\CMS\Core\Information\Typo3Version;

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
        if ((new Typo3Version())->getMajorVersion() == 11) {
            return new V11LanguageBootstrap();
        } else {
            return new V11LanguageBootstrap();
        }
    }
}
