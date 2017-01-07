<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07.01.17
 * Time: 11:50
 */

namespace Cundd\Rest\Tests\Manual\Api;


use Cundd\Rest\Tests\Manual\HttpClient;

abstract class AbstractApiCase extends \PHPUnit_Framework_TestCase
{
    public function request($path, $method = 'GET', $body = null, array $headers = [])
    {
        return HttpClient::client()->request($path, $method, $body, $headers);
    }

    public function requestJson($path, $method = 'GET', $body = null, array $headers = [])
    {
        return HttpClient::client()->requestJson($path, $method, $body, $headers);
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
     * @param $response
     * @return string
     */
    protected function getErrorDescription($response)
    {
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr($response->body, 0, 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error for request %s %s with response content: %s',
            $response->requestData->method,
            $response->requestData->url,
            $bodyPart
        );
    }
}