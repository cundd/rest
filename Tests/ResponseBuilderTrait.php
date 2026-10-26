<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Psr\Http\Message\ResponseInterface;
use Stringable;

trait ResponseBuilderTrait
{
    /**
     * @param int<100,599>                                  $status
     * @param array<non-empty-string, array<string>|string> $headers
     */
    public static function buildTestResponse(
        int $status,
        array $headers = [],
        string|Stringable|null $rawBody = null,
    ): ResponseInterface {
        return ResponseBuilderUtility::buildTestResponse(
            $status,
            $headers,
            $rawBody
        );
    }
}
