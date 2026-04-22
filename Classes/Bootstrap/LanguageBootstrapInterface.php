<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Psr\Http\Message\ServerRequestInterface;

interface LanguageBootstrapInterface
{
    /**
     * Set up the system to use the correct language
     */
    public function prepareRequest(
        ServerRequestInterface $request,
    ): ServerRequestInterface;
}
