<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07.01.17
 * Time: 11:50
 */

namespace Cundd\Rest\Tests\Manual\Api;

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
        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', reset($response->content), $this->getErrorDescription($response));

        $response = $this->requestJson('GeorgRinger-News-news' . $suffix);
        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', reset($response->content), $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getNewsTest($suffix = '')
    {
        $response = $this->requestJson('georg_ringer-news-news/1' . $suffix);

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('bodytext', $response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', $response->content, $this->getErrorDescription($response));
        $this->assertSame(1, $response->content['uid'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function getNewsNotFoundTest($suffix = '')
    {
        $response = $this->requestJson('georg_ringer-news-news/2300' . $suffix);

        $this->assertSame(404, $response->status, $this->getErrorDescription($response));
        $this->assertSame('{"error":"Not Found"}', $response->body, $this->getErrorDescription($response));
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

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', $response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('title', $response->content, $this->getErrorDescription($response));
        $this->assertSame($header, $response->content['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function addNewsWithIdTest($suffix = '')
    {
        $content = $this->getNewsData();
        $content['uid'] = 1;
        $response = $this->requestJson(
            'georg_ringer-news-news' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $this->assertSame(400, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertSame('{"error":"Bad Request"}', $response->body, $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
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

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', $response->content, $this->getErrorDescription($response));
        $this->assertSame($id, $response->content['uid'], $this->getErrorDescription($response));
        $this->assertSame($header, $response->content['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
     */
    public function updateNewsWithIdTest($suffix = '')
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

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertArrayHasKey('uid', $response->content, $this->getErrorDescription($response));
        $this->assertSame($id, $response->content['uid'], $this->getErrorDescription($response));
        $this->assertSame($header, $response->content['title'], $this->getErrorDescription($response));
    }

    /**
     * @test
     * @param string $suffix
     * @dataProvider suffixDataProvider
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

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertNotEmpty($response->content, $this->getErrorDescription($response));
        $this->assertSame('{"message":"Deleted"}', $response->body, $this->getErrorDescription($response));
    }

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

        return $response->content['uid'];
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