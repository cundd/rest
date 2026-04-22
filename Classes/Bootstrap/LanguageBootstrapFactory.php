<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

class LanguageBootstrapFactory
{
    public function build(): LanguageBootstrapInterface
    {
        return new LanguageBootstrap();
    }
}
