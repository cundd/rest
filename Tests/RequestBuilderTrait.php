<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

trait RequestBuilderTrait
{
    /**
     * @param string      $url
     * @param string|null $method
     * @param array       $params
     * @param array       $headers
     * @param mixed       $rawBody
     * @param array|null  $parsedBody
     * @param string|null $format
     * @return RestRequestInterface
     */
    public static function buildTestRequest(
        string $url,
        ?string $method = null,
        array $params = [],
        array $headers = [],
        $rawBody = null,
        ?array $parsedBody = null,
        ?string $format = null
    ): RestRequestInterface {
        $path = self::getPathFromUri($url);
        $resourceType = new ResourceType((string)strtok($path, '/'));

        if (null === $format) {
            $format = Format::DEFAULT_FORMAT;
        }
        if ($rawBody) {
            $stream = fopen('php://temp', 'a+');
            fputs($stream, (string)$rawBody);
        } else {
            $stream = 'php://input';
        }

        $uri = new Uri($url);
        $originalRequest = new ServerRequest(
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

        return new Request($originalRequest, $uri->withPath($path), $path, $resourceType, new Format($format));
    }

    private static function getPathFromUri(string $url): string
    {
        if ('http://' === substr($url, 0, 7) || 'https://' === substr($url, 0, 8)) {
            return (string)substr(strstr(substr($url, 8), '/'), 0);
        } else {
            return $url;
        }
    }
}
