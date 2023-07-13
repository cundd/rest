<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\V12\V12LanguageBootstrap;

class LanguageBootstrapFactory
{
    public function build(): LanguageBootstrapInterface
    {
        return new V12LanguageBootstrap();
    }
}
