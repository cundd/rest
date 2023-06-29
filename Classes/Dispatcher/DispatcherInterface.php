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
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request): ResponseInterface;

    /**
     * Dispatch the request
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RestRequestInterface $request): ResponseInterface;
}
