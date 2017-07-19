<?php

namespace Cundd\Rest\Tests;


use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

trait RequestBuilderTrait
{
    /**
     * @param string $url
     * @param string $method
     * @param array  $params
     * @param array  $headers
     * @param mixed  $rawBody
     * @param array  $parsedBody
     * @param string $format
     * @return RestRequestInterface
     */
    public static function buildTestRequest(
        $url,
        $method = null,
        array $params = [],
        array $headers = [],
        $rawBody = null,
        $parsedBody = null,
        $format = null
    ) {
        $path = $url;
//        $path = strtok($url, '/');
        $resourceType = new ResourceType((string)strtok($url, '/'));

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
}
