<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Stringable;
use UnexpectedValueException;

use function rewind;

final class ResponseBuilderUtility
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
        $stream = fopen('php://temp', 'a+');
        if (false === $stream) {
            throw new UnexpectedValueException('Could not open temp for writing');
        }

        if (null !== $rawBody) {
            if (false === fputs($stream, (string) $rawBody)) {
                throw new UnexpectedValueException('Could not write to temp stream');
            }
        }
        rewind($stream);

        return new Response($stream, $status, $headers);
    }
}
