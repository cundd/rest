<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request;
use Cundd\Rest\Request\Format;
use Cundd\Rest\Request\ResourceType;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use UnexpectedValueException;

use function array_pop;
use function basename;
use function explode;
use function implode;
use function strrpos;
use function substr;

final class RequestBuilderUtility
{
    private function __construct()
    {
    }

    /**
     * @param array<mixed,mixed>                            $params
     * @param array<non-empty-string, array<string>|string> $headers
     * @param array<mixed,mixed>|null                       $parsedBody
     */
    public static function buildTestRequest(
        string $url,
        ?string $method = null,
        array $params = [],
        array $headers = [],
        ?string $rawBody = null,
        ?array $parsedBody = null,
        ?string $format = null,
    ): RestRequestInterface {
        $path = self::getPathFromUri($url);
        $resourceType = new ResourceType((string) strtok($path, '/'));

        if (null === $format) {
            $resourceName = basename($path);
            if (false === strrpos($resourceName, '.')) {
                $format = Format::DEFAULT_FORMAT;
            } else {
                $pathParts = explode('.', $path);
                $format = array_pop($pathParts);
                $path = implode('.', $pathParts);
                if (!Format::isValidFormat($format)) {
                    $path .= '.' . $format;
                    $format = Format::DEFAULT_FORMAT;
                }
            }
        }

        $originalRequest = self::buildTestServerRequest(
            $url,
            $method,
            $params,
            $headers,
            $rawBody,
            $parsedBody
        );

        $uri = self::buildTestUri($url);

        return new Request(
            $originalRequest,
            $uri->withPath($path),
            $path,
            $resourceType,
            new Format($format)
        );
    }

    /**
     * @param array<mixed,mixed>                            $params
     * @param array<non-empty-string, array<string>|string> $headers
     * @param array<mixed,mixed>|null                       $parsedBody
     */
    public static function buildTestServerRequest(
        string $url,
        ?string $method = null,
        array $params = [],
        array $headers = [],
        ?string $rawBody = null,
        ?array $parsedBody = null,
    ): ServerRequestInterface {
        if ($rawBody) {
            $stream = fopen('php://temp', 'a+');
            if (false === $stream) {
                throw new UnexpectedValueException('Could not open temp for writing');
            }
            if (false === fputs($stream, (string) $rawBody)) {
                throw new UnexpectedValueException('Could not write to temp stream');
            }
        } else {
            $stream = 'php://input';
        }

        $uri = self::buildTestUri($url);

        return new ServerRequest(
            $_SERVER,
            [],
            $uri,
            $method,
            $stream,
            $headers,
            [],
            $params,
            $parsedBody ?: $_POST,
            '1.1'
        );
    }

    public static function buildTestUri(string $url): UriInterface
    {
        return new Uri($url);
    }

    private static function getPathFromUri(string $url): string
    {
        if ('http://' === substr($url, 0, 7) || 'https://' === substr($url, 0, 8)) {
            return (string) substr((string) strstr(substr($url, 8), '/'), 0);
        }

        return $url;
    }
}
