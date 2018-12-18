<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual\Api;


class PageTest extends AbstractApiCase
{
    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getPagesTest($suffix = '')
    {
        $response = $this->requestJson('virtual_object-page' . $suffix);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBody = $response->getParsedBody();
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', reset($parsedBody), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getPageTest($suffix = '')
    {
        $response = $this->requestJson('virtual_object-page/1' . $suffix);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(1, $response->getParsedBody()['id'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getPageNotFoundTest($suffix = '')
    {
        $response = $this->requestJson('virtual_object-page/2300' . $suffix);

        $this->assertSame(404, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertSame('{"error":"Not Found"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function addPageTest($suffix = '')
    {
        $title = 'A new page ' . date('Y-m-d H:i:s');
        $page = [
            'title'          => $title,
            'pageIdentifier' => 1, // the parent
        ];
        $response = $this->requestJson(
            'virtual_object-page' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame($title, $response->getParsedBody()['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function addPageWithIdTest($suffix = '')
    {
        $title = 'A new page ' . date('Y-m-d H:i:s');
        $page = [
            'id'             => 100,
            'title'          => $title,
            'pageIdentifier' => 1, // the parent
        ];
        $response = $this->requestJson(
            'virtual_object-page' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(100, $response->getParsedBody()['id'], $this->getErrorDescription($response));
        $this->assertSame($title, $response->getParsedBody()['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function updatePageWithIdInUrlTest($suffix = '')
    {
        // Make sure the page exists
        $this->addPageWithIdTest();

        $title = 'Updated page ' . date('Y-m-d H:i:s');
        $page = [
            'title'          => $title,
            'pageIdentifier' => 1, // the parent
        ];
        $response = $this->requestJson(
            'virtual_object-page/100' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(100, $response->getParsedBody()['id'], $this->getErrorDescription($response));
        $this->assertSame($title, $response->getParsedBody()['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function updatePageWithIdTest($suffix = '')
    {
        // Make sure the page exists
        $this->addPageWithIdTest();

        $title = 'Updated page ' . date('Y-m-d H:i:s');
        $page = [
            'id'             => 100,
            'title'          => $title,
            'pageIdentifier' => 1, // the parent
        ];
        $response = $this->requestJson(
            'virtual_object-page' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(100, $response->getParsedBody()['id'], $this->getErrorDescription($response));
        $this->assertSame($title, $response->getParsedBody()['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function deletePageWithIdInUrlTest($suffix = '')
    {
        // Make sure the page exists
        $this->addPageWithIdTest();

        $response = $this->requestJson(
            'virtual_object-page/100' . $suffix,
            'DELETE',
            null,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('{"message":"Deleted"}', $response->getBody(), $this->getErrorDescription($response));
    }
}