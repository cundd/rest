<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Tests\Functional\Fixtures\CustHandler;

class CustomHandlerTest extends AbstractIntegrationCase
{
    public function setUp()
    {
        parent::setUp();
        $this->configurePath(
            'cust',
            [
                "path"         => "cust",
                "read"         => "allow",
                "write"        => "allow",
                "handlerClass" => CustHandler::class,
            ]
        );
    }

    /**
     * @test
     */
    public function getIndexTest()
    {
        $response = $this->dispatch($this->buildTestRequest('/cust'));

        $this->assertSame(
            '{"message":"GET Index"}',
            (string)$response->getBody(),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function getFooTest()
    {
        $response = $this->dispatch($this->buildTestRequest('/cust/foo'));

        $this->assertSame(
            '{"message":"GET Foo"}',
            (string)$response->getBody(),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function postBarTest()
    {
        $response = $this->dispatch($this->buildTestRequest('/cust/bar', 'POST'));

        $this->assertSame(
            '{"message":"POST Bar"}',
            (string)$response->getBody(),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
        $this->assertSame(200, $response->getStatusCode());
    }


    /**
     * @test
     */
    public function postFooShouldFailTest()
    {
        $response = $this->dispatch($this->buildTestRequest('/cust/foo', 'POST'));

        $this->assertSame(
            '{"error":"Not Found"}',
            (string)$response->getBody(),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function getBarShouldFailTest()
    {
        $response = $this->dispatch($this->buildTestRequest('/cust/bar'));

        $this->assertSame(
            '{"error":"Not Found"}',
            (string)$response->getBody(),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
        $this->assertSame(404, $response->getStatusCode());
    }
}
