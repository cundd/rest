<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Factory class to get the current Request
 */
interface RequestFactoryInterface
{
    /**
     * Build the prepared REST Request for the given Server Request
     *
     * @param ServerRequestInterface $request
     * @return RestRequestInterface
     */
    public function buildRequest(ServerRequestInterface $request): RestRequestInterface;
}
