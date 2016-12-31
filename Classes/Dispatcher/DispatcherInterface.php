<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.08.15
 * Time: 13:22
 */
namespace Cundd\Rest\Dispatcher;

use Bullet\Response;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request;
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
     * @param ResponseInterface      $response
     * @return ResponseInterface
     */
    public function processRequest(ServerRequestInterface $request, ResponseInterface $response);

    /**
     * Dispatch the request
     *
     * @param RestRequestInterface $request
     * @param ResponseInterface    $response
     * @return ResponseInterface
     */
    public function dispatch(RestRequestInterface $request, ResponseInterface $response);
}
