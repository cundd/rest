<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use Laminas\Diactoros\Response;
use function rewind;

trait ResponseBuilderTrait
{
    /**
     * @param int   $status
     * @param array $headers
     * @param mixed $rawBody
     * @return ResponseInterface
     */
    public static function buildTestResponse($status, array $headers = [], $rawBody = null): ResponseInterface
    {
        if ($rawBody) {
            $stream = fopen('php://temp', 'a+');
            if (false === fputs($stream, (string)$rawBody)) {
                throw new UnexpectedValueException('Could not write to stream');
            }
            rewind($stream);
        } else {
            $stream = 'php://input';
        }

        return new Response($stream, $status, $headers);
    }
}
