<?php

namespace Cundd\Rest;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory class to create Response objects
 */
interface ResponseFactoryInterface
{
    /**
     * Returns a response with the given content and status code
     *
     * @param string|array $data   Data to send
     * @param int          $status Status code of the response
     * @return ResponseInterface
     */
    public function createResponse($data, $status);

    /**
     * Returns a response with the given message and status code
     *
     * Some data (e.g. the format) will be read from the request. If no explicit request is given, the Request Factory
     * will be queried
     *
     * @param string|array         $data
     * @param int                  $status
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function createErrorResponse($data, $status, RestRequestInterface $request);

    /**
     * Returns a response with the given message and status code
     *
     * Some data (e.g. the format) will be read from the request. If no explicit request is given, the Request Factory
     * will be queried
     *
     * @param string|array         $data
     * @param int                  $status
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function createSuccessResponse($data, $status, RestRequestInterface $request);
}
