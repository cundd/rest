<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual\Api;

/**
 * Test the custom routing using the custom_rest extension (https://github.com/cundd/custom_rest)
 */
class CustomRestTest extends AbstractApiCase
{
    /**
     * @test
     */
    public function getWithoutTrailingSlashTest()
    {
        $path = 'cundd-custom_rest-route';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route',
            $response->getParsedBody()['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->getParsedBody()['resourceType'],
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

        $this->assertSame(404, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('{"error":"Not Found"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function getWithFormatTest()
    {
        $path = 'cundd-custom_rest-route.json';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route',
            $response->getParsedBody()['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->getParsedBody()['resourceType'],
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

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $response->getParsedBody()['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->getParsedBody()['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithParameterSlugTest()
    {
        $path = 'cundd-custom_rest-route/parameter/slug.json';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('slug', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('slug', $response->getParsedBody()['slug'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/parameter/slug',
            $response->getParsedBody()['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->getParsedBody()['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithParameterIntegerTest()
    {
        $path = 'cundd-custom_rest-route/12.json';
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            'integer',
            $response->getParsedBody()['parameterType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(12, $response->getParsedBody()['value'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @dataProvider getWithParameterFloatDataProvider
     * @param string $suffix
     */
    public function getWithParameterFloatTest($suffix)
    {
        $path = 'cundd-custom_rest-route/decimal/12.0' . $suffix;
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('double', $response->getParsedBody()['parameterType'], $this->getErrorDescription($response));
        $this->assertSame(12, $response->getParsedBody()['value'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
    }

    public function getWithParameterFloatDataProvider()
    {
        return [
            [''],
            ['.json'],
        ];
    }

    /**
     * @test
     * @dataProvider boolSuffixDataProvider
     * @param $suffix
     * @param $expected
     */
    public function getWithParameterBoolTest($suffix, $expected)
    {
        $path = 'cundd-custom_rest-route/bool/' . $suffix;
        $response = $this->requestJson($path);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            'boolean',
            $response->getParsedBody()['parameterType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame($expected, $response->getParsedBody()['value'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
    }

    public function boolSuffixDataProvider()
    {
        return [
            ['yes', true],
            ['true', true],
            ['on', true],
            ['1', true],
            ['no', false],
            ['false', false],
            ['off', false],
            ['0', false],
        ];
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

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $response->getParsedBody()['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->getParsedBody()['resourceType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            $data,
            $response->getParsedBody()['data'],
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

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $response->getParsedBody()['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $response->getParsedBody()['resourceType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            $data,
            $response->getParsedBody()['data'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function runExtbaseTest()
    {
        $path = 'cundd-custom_rest-route/create';
        $data = [
            'firstName' => 'john',
            'lastName'  => 'john',
        ];
        $response = $this->requestJson(
            $path,
            'POST',
            $data,
            [
                'Content-Type' => 'application/json',
            ]
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('{"success":1}', $response->getBody(), $this->getErrorDescription($response));
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @test
     */
    public function unauthorizedTest()
    {
        $path = 'cundd-custom_rest-require';
        $response = $this->requestJson($path);

        $this->assertSame(401, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('{"error":"Unauthorized"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function authorizeTest()
    {
        $path = 'cundd-custom_rest-require';
        $response = $this->requestJson($path, 'GET', null, [], $this->getApiUser() . ':' . $this->getApiKey());

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('{"message":"Access Granted"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function getForbiddenTest()
    {
        $response = $this->request('cundd-custom_rest-require');
        $this->assertSame(401, $response->getStatusCode());
    }

    /**
     * @test
     * @param string $path
     * @param int    $expectedStatus
     * @dataProvider differentTestsDataProvider
     */
    public function differentTests($path, $expectedStatus)
    {
        $response = $this->requestJson($path);
        $this->assertSame($expectedStatus, $response->getStatusCode(), $this->getErrorDescription($response));
    }

    /**
     * @return array
     */
    public function differentTestsDataProvider()
    {
        return [
            ['customhandler', 200],
            ['customhandler/subpath', 200],
            ['customhandler/parameter/slug', 200],
            ['customhandler/12', 200],
            ['customhandler/decimal/10.8', 200],
            ['customhandler/bool/yes', 200],
            ['customhandler/bool/no', 200],
            ['cundd-custom_rest-person', 200],
            ['cundd-custom_rest-person/show/1', 200],
            ['cundd-custom_rest-person/firstname/daniel', 200],
            ['cundd-custom_rest-person/lastname/corn', 200],
            ['cundd-custom_rest-person/birthday/0000-00-00', 200],
            ['cundd-custom_rest-person/show', 200],
            ['cundd-custom_rest-person/lastname', 404],
            ['cundd-custom_rest-person/firstname', 404],
        ];
    }
}
