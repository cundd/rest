<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07.01.17
 * Time: 11:50
 */

namespace Cundd\Rest\Tests\Manual\Api;


class CustomRestTest extends AbstractApiCase
{
    /**
     * @test
     */
    public function getWithoutTrailingSlashTest()
    {
        $path = 'cundd-custom_rest-route';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->content, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route',
            $response->content['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->content['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithTrailingSlashTest()
    {
        $path = 'cundd-custom_rest-route/';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertSame('{"message":"OK"}', $response->body, $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function getWithFormatTest()
    {
        $path = 'cundd-custom_rest-route.json';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->content, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route',
            $response->content['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->content['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithSubpathTest()
    {
        $path = 'cundd-custom_rest-route/subpath.json';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->content, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $response->content['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->content['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function postDataTest()
    {
        $path = 'cundd-custom_rest-route/subpath.json';
        $data = [
            'user'  => 'Daniel',
            'hobby' => 'playing guitar',
        ];
        $response = $this->requestJson($path, 'POST', $data, ['Content-Type' => 'application/json']);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->content, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $response->content['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->content['resourceType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            $data,
            $response->content['data'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function postDataUrlEncodedTest()
    {
        $path = 'cundd-custom_rest-route/subpath.json';
        $data = [
            'user'  => 'Daniel',
            'hobby' => 'playing guitar',
        ];
        $response = $this->requestJson(
            $path,
            'POST',
            http_build_query($data),
            [
                "Content-Type" => "application/x-www-form-urlencoded",
            ]
        );

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->content, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $response->content['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->content['resourceType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            $data,
            $response->content['data'],
            $this->getErrorDescription($response)
        );
    }
}
