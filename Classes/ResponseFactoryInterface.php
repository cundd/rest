<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory class to create Response objects
 */
interface ResponseFactoryInterface
{
    /**
     * Return a response with the given content and status code
     *
     * @param string $data   Data to send
     * @param int    $status Status code of the response
     */
    public function createResponse(
        string $data,
        int $status,
    ): ResponseInterface;

    /**
     * Return a response with the given message and status code
     *
     * Some data (e.g. the format) will be read from the request.
     *
     * @param string|int|array<mixed>|null $data
     */
    public function createErrorResponse(
        string|int|array|null $data,
        int $status,
        RestRequestInterface $request,
    ): ResponseInterface;

    /**
     * Return a response with the given message and status code
     *
     * Some data (e.g. the format) will be read from the request.
     *
     * @param string|int|array<mixed>|null $data
     */
    public function createSuccessResponse(
        string|int|array|null $data,
        int $status,
        RestRequestInterface $request,
    ): ResponseInterface;
}
