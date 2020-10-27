<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Fixtures;

use Cundd\Rest\Tests\ResponseBuilderTrait;
use InvalidArgumentException;
use Nimut\TestingFramework\Http\Response as NimutResponse;
use Psr\Http\Message\ResponseInterface;
use function preg_match;
use function strlen;
use function substr;

class TestResponseFactory
{
    /**
     * @param NimutResponse|ResponseInterface $response
     * @return ResponseInterface
     */
    public static function fromResponse($response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return clone $response;
        } elseif ($response instanceof NimutResponse) {
            $statusCode = self::detectStatusCodeForNimutResponse($response);

            return ResponseBuilderTrait::buildTestResponse($statusCode, [], $response->getContent());
        }

        throw new InvalidArgumentException();
    }

    /**
     * @param NimutResponse $response
     * @return int
     */
    private static function detectStatusCodeForNimutResponse(NimutResponse $response): int
    {
        switch ($response->getStatus()) {
            case NimutResponse::STATUS_Success :
            case 'success':
                $statusCode = 200;
                break;
            case NimutResponse::STATUS_Failure :
            case 'failure':
                $statusCode = 400;
                break;
            default:
                $statusCode = 500;
        }

        if ($statusCode === 200) {
            $content = $response->getContent();

            if (static::hasPrefix($content, '{"error":"Please add a last name:')) {
                return 404;
            }
            if (static::hasPrefix($content, '{"error":"Please add a first name:')) {
                return 404;
            }
            if ($content === '{"error":"Unauthorized"}') {
                return 401;
            }
            if ($content === '{"error":"Forbidden"}') {
                return 403;
            }
            if ($content === '{"error":"Not Found"}') {
                return 404;
            }
            if (0 !== preg_match('!{"error":"Route .* not found for method .*}!', $content)) {
                return 404;
            }
            //                {"error":"Route \"\/cundd-custom_rest-route\/\" not found for method \"GET\""}
        }

        return $statusCode;
    }

    private static function hasPrefix(string $text, string $prefix): bool
    {
        $length = strlen($prefix);

        return substr($text, 0, $length) === $prefix;
    }
}
