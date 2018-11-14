<?php

namespace Cundd\Rest\Tests\Manual\Api;


use Cundd\Rest\Tests\Manual\HttpClient;
use Cundd\Rest\Tests\Manual\HttpErrorDescriptionTrait;
use Cundd\Rest\Tests\Manual\HttpResponse;

abstract class AbstractApiCase extends \PHPUnit\Framework\TestCase
{
    use HttpErrorDescriptionTrait;

    /**
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     * @return HttpResponse
     */
    public function request($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        return HttpClient::client()->request($path, $method, $body, $headers, $basicAuth);
    }

    /**
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     * @return HttpResponse
     */
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
}