<?php

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
     * @param ResponseInterface      $response Prepared response @deprecated will be removed in 4.0.0
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response = null);

    /**
     * Dispatch the request
     *
     * @param RestRequestInterface $request
     * @param ResponseInterface    $response Prepared response @deprecated will be removed in 4.0.0
     * @return ResponseInterface
     */
    public function dispatch(RestRequestInterface $request, ResponseInterface $response = null);
}
