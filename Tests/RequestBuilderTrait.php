<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Cundd\Rest\Http\RestRequestInterface;

trait RequestBuilderTrait
{
    /**
     * @param array<mixed,mixed>      $params
     * @param array<mixed,mixed>      $headers
     * @param array<mixed,mixed>|null $parsedBody
     */
    public function buildTestRequest(
        string $url,
        ?string $method = null,
        array $params = [],
        array $headers = [],
        ?string $rawBody = null,
        ?array $parsedBody = null,
        ?string $format = null,
    ): RestRequestInterface {
        return RequestBuilderUtility::buildTestRequest(
            $url,
            $method,
            $params,
            $headers,
            $rawBody,
            $parsedBody,
            $format,
        );
    }
}
