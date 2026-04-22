<?php

declare(strict_types=1);

namespace Cundd\Rest\Dispatcher;

use Psr\Http\Message\ResponseInterface;

/**
 * @phpstan-type HeaderValue int|string|array{userFunc:non-empty-string}|array<mixed,string>
 */
interface ResponseHeaderUpdaterInterface
{
    /**
     * @param array<string,HeaderValue> $headers
     */
    public function addHeaders(
        ResponseInterface $response,
        array $headers,
        bool $overwrite,
    ): ResponseInterface;
}
