<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07.01.17
 * Time: 11:50
 */

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
        $response = $this->requestJson('VirtualObject-Page' . $suffix);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', reset($response->content), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getPageTest($suffix = '')
    {
        $response = $this->requestJson('VirtualObject-Page/1' . $suffix);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->content, $this->getErrorDescription($response));
        $this->assertSame(1, $response->content['id'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getPageNotFoundTest($suffix = '')
    {
        $response = $this->requestJson('VirtualObject-Page/2300' . $suffix);

        $this->assertSame(404, $response->status, $this->getErrorDescription($response));
        $this->assertSame('{"error":"Not Found"}', $response->body, $this->getErrorDescription($response));
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
            'VirtualObject-Page' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->content, $this->getErrorDescription($response));
        $this->assertSame($title, $response->content['title'], $this->getErrorDescription($response));
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
            'VirtualObject-Page' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->content, $this->getErrorDescription($response));
        $this->assertSame(100, $response->content['id'], $this->getErrorDescription($response));
        $this->assertSame($title, $response->content['title'], $this->getErrorDescription($response));
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
            'VirtualObject-Page/100' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->content, $this->getErrorDescription($response));
        $this->assertSame(100, $response->content['id'], $this->getErrorDescription($response));
        $this->assertSame($title, $response->content['title'], $this->getErrorDescription($response));
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
            'VirtualObject-Page' . $suffix,
            'POST',
            $page,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->content, $this->getErrorDescription($response));
        $this->assertSame(100, $response->content['id'], $this->getErrorDescription($response));
        $this->assertSame($title, $response->content['title'], $this->getErrorDescription($response));
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
            'VirtualObject-Page/100' . $suffix,
            'DELETE',
            null,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertSame('{"message":"Deleted"}', $response->body, $this->getErrorDescription($response));
    }
}