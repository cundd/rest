<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V11\V11LanguageBootstrap;
use Cundd\Rest\Bootstrap\V12\V12LanguageBootstrap;
use TYPO3\CMS\Core\Information\Typo3Version;

class LanguageBootstrapFactory
{
    public function build(): LanguageBootstrapInterface
    {
        if ((new Typo3Version())->getMajorVersion() == 11) {
            return new V11LanguageBootstrap();
        } else {
            return new V12LanguageBootstrap();
        }
    }
}
