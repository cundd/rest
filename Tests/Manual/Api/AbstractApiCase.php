<?php

namespace Cundd\Rest\Tests\Manual\Api;


use Cundd\Rest\Tests\Manual\HttpClient;

abstract class AbstractApiCase extends \PHPUnit\Framework\TestCase
{
    public function request($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        return HttpClient::client()->request($path, $method, $body, $headers, $basicAuth);
    }

    public function requestJson($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        return HttpClient::client()->requestJson($path, $method, $body, $headers, $basicAuth);
    }

    public function suffixDataProvider()
    {
        return [
            [''],
            ['/'],
            ['.json'],
        ];
    }

    /**
     * @return string
     */
    protected function getApiUser()
    {
        return getenv('API_USER') ?: 'daniel';
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        return getenv('API_KEY') ?: 'api-key';
    }

    /**
     * @param $response
     * @return string
     */
    protected function getErrorDescription($response)
    {
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr($response->body, 0, getenv('ERROR_BODY_LENGTH') ?: 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error for request %s %s with response content: %s',
            $response->requestData->method,
            $response->requestData->url,
            $bodyPart
        );
    }
}