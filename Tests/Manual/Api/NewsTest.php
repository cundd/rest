<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual\Api;

use Exception;

/**
 * Test the default Data Provider using the News extension (https://github.com/georgringer/news)
 */
class NewsTest extends AbstractApiCase
{
    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getNewsCollectionTest($suffix = '')
    {
        $response = $this->requestJson('georg_ringer-news-news' . $suffix);
        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBodyLowerCase = $response->getParsedBody();
        $this->assertNotEmpty($parsedBodyLowerCase, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', reset($parsedBodyLowerCase), $this->getErrorDescription($response));

        $response = $this->requestJson('GeorgRinger-News-news' . $suffix);
        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBodyUpperCase = $response->getParsedBody();
        $this->assertNotEmpty($parsedBodyUpperCase, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', reset($parsedBodyUpperCase), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getNewsTest($suffix = '')
    {
        $response = $this->requestJson('georg_ringer-news-news/1' . $suffix);

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('bodytext', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(1, $response->getParsedBody()['uid'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getNewsNotFoundTest($suffix = '')
    {
        $response = $this->requestJson('georg_ringer-news-news/2300' . $suffix);

        $this->assertSame(404, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertSame('{"error":"Not Found"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function addNewsTest($suffix = '')
    {
        $header = 'A new Content ' . date('Y-m-d H:i:s');
        $content = $this->getNewsData();
        $content['title'] = $header;
        $response = $this->requestJson(
            'georg_ringer-news-news' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $parsedBody = $response->getParsedBody();
        $this->assertNotEmpty($parsedBody, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', $parsedBody, $this->getErrorDescription($response));
        $this->assertIsInt($parsedBody['uid'], $this->getErrorDescription($response));
        $this->assertArrayHasKey('title', $parsedBody, $this->getErrorDescription($response));
        $this->assertSame($header, $parsedBody['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function addNewsWithIdShouldFailTest($suffix = '')
    {
        $content = $this->getNewsData();
        $content['uid'] = 1;
        $response = $this->requestJson(
            'georg_ringer-news-news' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(400, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '{"error":"Invalid property \"uid\""}',
            $response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     * @throws Exception
     */
    public function updateNewsWithIdInUrlTest($suffix = '')
    {
        $id = $this->addNewsAndGetId();
        $header = 'Updated Content ' . date('Y-m-d H:i:s');
        $content = $this->getNewsData();
        $content['title'] = $header;

        $response = $this->requestJson(
            'georg_ringer-news-news/' . $id . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', $response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame($id, $response->getParsedBody()['uid'], $this->getErrorDescription($response));
        $this->assertSame($header, $response->getParsedBody()['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     * @throws Exception
     */
    public function updateNewsWithIdShouldFailTest($suffix = '')
    {
        $id = $this->addNewsAndGetId();
        $header = 'Updated Content ' . date('Y-m-d H:i:s');
        $content = $this->getNewsData();
        $content['title'] = $header;
        $content['__identity'] = $id;

        $response = $this->requestJson(
            'georg_ringer-news-news' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(400, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame(
            '{"error":"Invalid property \"__identity\""}',
            $response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     * @throws Exception
     */
    public function deleteNewsWithIdInUrlTest($suffix = '')
    {
        // Make sure the News entry exists
        $id = $this->addNewsAndGetId();

        $response = $this->requestJson(
            'georg_ringer-news-news/' . $id . $suffix,
            'DELETE',
            null,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getParsedBody(), $this->getErrorDescription($response));
        $this->assertSame('{"message":"Deleted"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function addNewsAndGetId()
    {
        $content = $this->getNewsData();
        $content['title'] = 'New added news entry from ' . date('Y-m-d H:i:s');
        $response = $this->requestJson(
            'georg_ringer-news-news',
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        if (!isset($response->getParsedBody()['uid'])) {
            throw new Exception('Content does not contain key "uid"');
        }

        return $response->getParsedBody()['uid'];
    }

    private function getNewsData()
    {
        return [
            'hidden'           => false,
            'deleted'          => null,
            'title'            => 'The title',
            'alternativeTitle' => '',
            'teaser'           => '',
            'bodytext'         => 'The body text',
            'archive'          => null,
            'author'           => '',
            'authorEmail'      => '',
            'type'             => '0',
            'keywords'         => '',
            'description'      => '',
            'sorting'          => 0,
            'pid'              => 1,
        ];
    }
}
