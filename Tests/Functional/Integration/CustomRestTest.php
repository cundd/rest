<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use function base64_encode;
use function putenv;

/**
 * Test the custom routing using the custom_rest extension (https://github.com/cundd/custom_rest)
 */
class CustomRestTest extends AbstractIntegrationCase
{
    use ImportPagesTrait;
    use FrontendSiteSetupTrait;

    private const ROOT_PAGE_ID = 1;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/rest',
        'typo3conf/ext/rest/Tests/Functional/Fixtures/Extensions/custom_rest',
    ];

    public function setUp(): void
    {
        parent::setUp();

        //        $this->importCSVDataSet(__DIR__. '/../Fixtures/sys_language.csv');
        //        $this->importCSVDataSet(__DIR__. '/../Fixtures/pages.csv');
        //        $this->importDataSet('ntf://Database/tt_content.xml');

        $this->importDataSet(__DIR__ . '/../Fixtures/login.xml');
        $this->importPages();

        // Set up the page with uid 1 and include the TypoScript as sys_template record
        $this->setUpFrontendRootPage(
            self::ROOT_PAGE_ID,
            [
                __DIR__ . '/../../Configuration/TypoScript/Configuration.typoscript',
                __DIR__ . '/../Fixtures/Extensions/custom_rest/Configuration/TypoScript/setup.typoscript',
            ]
        );
        $this->setUpFrontendSite(self::ROOT_PAGE_ID, $this->siteLanguageConfiguration);
    }

    /**
     * @test
     */
    public function getWithoutTrailingSlashTest()
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-route';
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);
        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBody = $this->getParsedBody($response);
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route',
            $parsedBody['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $parsedBody['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithTrailingSlashTest()
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        // Disable TEST_MODE to get the additional information in the error message
        putenv('TEST_MODE=');
        $path = 'cundd-custom_rest-route/';
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);

        $this->assertSame(404, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($this->getParsedBody($response), $this->getErrorDescription($response));
        $this->assertSame(
            '{"error":"Route \"\/cundd-custom_rest-route\/\" not found for method \"GET\""}',
            (string)$response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithFormatTest()
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-route.json';
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBody = $this->getParsedBody($response);
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route',
            $parsedBody['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $parsedBody['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithSubpathTest()
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-route/subpath.json';
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBody = $this->getParsedBody($response);
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $parsedBody['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $parsedBody['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithParameterSlugTest()
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-route/parameter/slug.json';
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);
        $parsedBody = $this->getParsedBody($response);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertArrayHasKey('slug', $parsedBody, $this->getErrorDescription($response));
        $this->assertSame('slug', $parsedBody['slug'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/parameter/slug',
            $parsedBody['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $parsedBody['resourceType'],
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getWithParameterIntegerTest()
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-route/12.json';
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);
        $parsedBody = $this->getParsedBody($response);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            'integer',
            $parsedBody['parameterType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(12, $parsedBody['value'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
    }

    /**
     * @test
     * @dataProvider getWithParameterFloatDataProvider
     * @param string $suffix
     */
    public function getWithParameterFloatTest(string $suffix)
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-route/decimal/12.0' . $suffix;
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);
        $parsedBody = $this->getParsedBody($response);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertSame('double', $parsedBody['parameterType'], $this->getErrorDescription($response));
        $this->assertSame(12, $parsedBody['value'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
    }

    public function getWithParameterFloatDataProvider(): array
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
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-route/bool/' . $suffix;
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);
        $parsedBody = $this->getParsedBody($response);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            'boolean',
            $parsedBody['parameterType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame($expected, $parsedBody['value'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
    }

    public function boolSuffixDataProvider(): array
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
        $path = 'cundd-custom_rest-route/subpath';
        $data = [
            'user' => 'Daniel',
            'hobby' => 'playing guitar',
        ];
        $response = $this->buildRequestAndDispatch(
            $this->getContainer(),
            $path,
            'POST',
            $data,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBody = $this->getParsedBody($response);
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertArrayHasKey('path', $parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            '/cundd-custom_rest-route/subpath',
            $parsedBody['path'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            'cundd-custom_rest-route',
            $parsedBody['resourceType'],
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            $data,
            $parsedBody['data'],
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
        $response = $this->buildRequestAndDispatch(
            $this->getContainer(),
            $path,
            'POST',
            $data,
            [
                'Content-Type' => 'application/json',
            ]
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($this->getParsedBody($response), $this->getErrorDescription($response));
        $this->assertSame('{"success":1}', $response->getBody(), $this->getErrorDescription($response));
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @test
     */
    public function unauthorizedTest()
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $path = 'cundd-custom_rest-require';
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);
        $parsedBody = $this->getParsedBody($response);

        $this->assertSame(401, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertSame(
            '{"error":"Unauthorized"}',
            (string)$response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function authorizeTest()
    {
        // TODO: Mock the Session Manager
        $this->markTestIncomplete('Not implemented for Functional Tests');
        $path = 'cundd-custom_rest-require';
        $response = $this->buildRequestAndDispatch(
            $this->getContainer(),
            $path,
            'GET',
            null,
            ['Authorization' => 'Basic ' . base64_encode('daniel:api-key')]
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($this->getParsedBody($response), $this->getErrorDescription($response));
        $this->assertSame(
            '{"message":"Access Granted"}',
            (string)$response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     */
    public function getForbiddenTest()
    {
        // TODO: Mock the Session Manager
        $this->markTestIncomplete('Not implemented for Functional Tests');
        //        $response = $this->buildRequestAndDispatch($this->buildConfiguredObjectManager(), 'cundd-custom_rest-require');
        $response = $this->buildRequestAndDispatch($this->getContainer(), 'cundd-custom_rest-require');
        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * @test
     * @param string $path
     * @param int    $expectedStatus
     * @dataProvider differentTestsDataProvider
     */
    public function differentTests(string $path, int $expectedStatus)
    {
        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        $response = $this->fetchFrontendResponse('/rest/' . $path, self::ROOT_PAGE_ID);
        $this->assertSame($expectedStatus, $response->getStatusCode(), $this->getErrorDescription($response));
    }

    /**
     * @return array
     */
    public function differentTestsDataProvider(): array
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
