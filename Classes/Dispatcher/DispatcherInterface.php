<?php

declare(strict_types=1);

namespace Cundd\Rest\Dispatcher;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for the main dispatcher of REST requests
 */
interface DispatcherInterface
{
    /**
     * Process the raw request
     *
     * Entry point for the PSR 7 middleware
     */
    public function processRequest(ServerRequestInterface $request): ResponseInterface;

    /**
     * Dispatch the request
     */
    public function dispatch(RestRequestInterface $request): ResponseInterface;
}
