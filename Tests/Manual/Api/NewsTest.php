<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual\Api;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test the default Data Provider using the News extension (https://github.com/georgringer/news)
 */
class NewsTest extends AbstractApiCase
{
    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function getNewsCollectionTest(string $suffix = ''): void
    {
        $response = $this->requestJson('georg_ringer-news-news' . $suffix);
        $errorDescription = $this->getErrorDescription($response);
        $this->assertSame(200, $response->getStatusCode(), $errorDescription);
        $parsedBodyLowerCase = $response->getParsedBody();
        $this->assertIsArray($parsedBodyLowerCase, $errorDescription);
        $this->assertNotEmpty($parsedBodyLowerCase, $errorDescription);
        $this->assertArrayHasKey('uid', reset($parsedBodyLowerCase), $errorDescription);

        $response = $this->requestJson('GeorgRinger-News-news' . $suffix);
        $this->assertSame(200, $response->getStatusCode(), $errorDescription);
        $parsedBodyUpperCase = $response->getParsedBody();
        $this->assertIsArray($parsedBodyUpperCase, $errorDescription);
        $this->assertNotEmpty($parsedBodyUpperCase, $errorDescription);
        $this->assertArrayHasKey('uid', reset($parsedBodyUpperCase), $errorDescription);
    }

    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function getNewsTest(string $suffix = ''): void
    {
        $response = $this->requestJson('georg_ringer-news-news/1' . $suffix);

        $errorDescription = $this->getErrorDescription($response);
        $this->assertSame(200, $response->getStatusCode(), $errorDescription);
        $this->assertNotEmpty($response->getParsedBody(), $errorDescription);
        $this->assertIsArray($response->getParsedBody(), $errorDescription);
        $this->assertArrayHasKey('bodytext', $response->getParsedBody(), $errorDescription);
        $this->assertArrayHasKey('uid', $response->getParsedBody(), $errorDescription);
        $this->assertSame(1, $response->getParsedBody()['uid'], $errorDescription);
    }

    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function getNewsNotFoundTest(string $suffix = ''): void
    {
        $response = $this->requestJson('georg_ringer-news-news/2300' . $suffix);

        $this->assertSame(
            404,
            $response->getStatusCode(),
            $this->getErrorDescription($response)
        );
        $this->assertSame(
            '{"error":"Not Found"}',
            $response->getBody(),
            $this->getErrorDescription($response)
        );
    }

    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function addNewsTest(string $suffix = ''): void
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

        $errorDescription = $this->getErrorDescription($response);
        $this->assertSame(200, $response->getStatusCode(), $errorDescription);
        $parsedBody = $response->getParsedBody();
        $this->assertIsArray($parsedBody, $errorDescription);
        $this->assertNotEmpty($parsedBody, $errorDescription);
        $this->assertArrayHasKey('uid', $parsedBody, $errorDescription);
        $this->assertIsInt($parsedBody['uid'], $errorDescription);
        $this->assertArrayHasKey('title', $parsedBody, $errorDescription);
        $this->assertSame($header, $parsedBody['title'], $errorDescription);
    }

    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function addNewsWithIdShouldFailTest(string $suffix = ''): void
    {
        $content = $this->getNewsData();
        $content['uid'] = 1;
        $response = $this->requestJson(
            'georg_ringer-news-news' . $suffix,
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        $errorDescription = $this->getErrorDescription($response);
        $this->assertSame(400, $response->getStatusCode(), $errorDescription);
        $this->assertNotEmpty($response->getParsedBody(), $errorDescription);
        $this->assertSame(
            '{"error":"Invalid property \"uid\""}',
            $response->getBody(),
            $errorDescription
        );
    }

    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function updateNewsWithIdInUrlTest(string $suffix = ''): void
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

        $errorDescription = $this->getErrorDescription($response);
        $this->assertSame(200, $response->getStatusCode(), $errorDescription);
        $this->assertIsArray($response->getParsedBody(), $errorDescription);
        $this->assertNotEmpty($response->getParsedBody(), $errorDescription);
        $this->assertArrayHasKey('uid', $response->getParsedBody(), $errorDescription);
        $this->assertSame($id, $response->getParsedBody()['uid'], $errorDescription);
        $this->assertSame($header, $response->getParsedBody()['title'], $errorDescription);
    }

    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function updateNewsWithIdShouldFailTest(string $suffix = ''): void
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

    #[Test]
    #[DataProvider('suffixDataProvider')]
    public function deleteNewsWithIdInUrlTest(string $suffix = ''): void
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
     * @throws Exception
     */
    private function addNewsAndGetId(): int
    {
        $content = $this->getNewsData();
        $content['title'] = 'New added news entry from ' . date('Y-m-d H:i:s');
        $response = $this->requestJson(
            'georg_ringer-news-news',
            'POST',
            $content,
            ['Content-Type' => 'application/json']
        );

        if (!is_array($response->getParsedBody())) {
            throw new Exception('Expected parsed body to be an array');
        }
        if (!isset($response->getParsedBody()['uid'])) {
            throw new Exception('Content does not contain key "uid"');
        }

        return $response->getParsedBody()['uid'];
    }

    /**
     * @return array<string,false|string|int|null>
     */
    private function getNewsData(): array
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
