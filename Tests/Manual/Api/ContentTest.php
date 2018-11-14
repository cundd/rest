<?php

namespace Cundd\Rest\Tests\Manual\Api;


class ContentTest extends AbstractApiCase
{
    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getContentsTest($suffix = '')
    {
        $response = $this->requestJson('VirtualObject-Content' . $suffix);

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
    public function getContentTest($suffix = '')
    {
        $response = $this->requestJson('VirtualObject-Content/1' . $suffix);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('bodytext', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(1, $response->getParsedBody()['id'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getContentNotFoundTest($suffix = '')
    {
        $response = $this->requestJson('VirtualObject-Content/2300' . $suffix);

        $this->assertSame(404, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertSame('{"error":"Not Found"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function addContentTest($suffix = '')
    {
        $header = 'A new Content ' . date('Y-m-d H:i:s');
        $content = [
            'header'         => $header,
            'pageIdentifier' => 1, // the parent
            'type'           => 'textmedia',
        ];
        $response = $this->requestJson(
            'VirtualObject-Content' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('header', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame($header, $response->getParsedBody()['header'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function addContentWithIdTest($suffix = '')
    {
        $header = 'A new Content ' . date('Y-m-d H:i:s');
        $content = [
            'id'             => 100,
            'header'         => $header,
            'pageIdentifier' => 1, // the parent
            'type'           => 'textmedia',
        ];
        $response = $this->requestJson(
            'VirtualObject-Content' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(100, $response->getParsedBody()['id'], $this->getErrorDescription($response));
        $this->assertSame($header, $response->getParsedBody()['header'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function updateContentWithIdInUrlTest($suffix = '')
    {
        // Make sure the Content exists
        $this->addContentWithIdTest();

        $header = 'Updated Content ' . date('Y-m-d H:i:s');
        $content = [
            'header'         => $header,
            'pageIdentifier' => 1, // the parent
            'type'           => 'textmedia',
        ];
        $response = $this->requestJson(
            'VirtualObject-Content/100' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(100, $response->getParsedBody()['id'], $this->getErrorDescription($response));
        $this->assertSame($header, $response->getParsedBody()['header'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function updateContentWithIdTest($suffix = '')
    {
        // Make sure the Content exists
        $this->addContentWithIdTest();

        $header = 'Updated Content ' . date('Y-m-d H:i:s');
        $content = [
            'id'             => 100,
            'header'         => $header,
            'pageIdentifier' => 1, // the parent
            'type'           => 'textmedia',
        ];
        $response = $this->requestJson(
            'VirtualObject-Content' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('id', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(100, $response->getParsedBody()['id'], $this->getErrorDescription($response));
        $this->assertSame($header, $response->getParsedBody()['header'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function deleteContentWithIdInUrlTest($suffix = '')
    {
        // Make sure the Content exists
        $this->addContentWithIdTest();

        $response = $this->requestJson(
            'VirtualObject-Content/100' . $suffix,
            'DELETE',
            null,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('{"message":"Deleted"}', $response->getBody(), $this->getErrorDescription($response));
    }
}