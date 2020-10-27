<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Tests\Functional\Fixtures\CustHandler;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class CustomHandlerTest extends AbstractIntegrationCase
{
    /**
     * @test
     */
    public function getIndexTest()
    {
        $objectManager = $this->buildConfiguredObjectManager();
        $this->configureHandlerPath($objectManager);
        $response = $this->dispatch(
            $objectManager,
            $this->buildTestRequest('/cust')
        );

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
        $objectManager = $this->buildConfiguredObjectManager();
        $this->configureHandlerPath($objectManager);
        $response = $this->dispatch(
            $objectManager,
            $this->buildTestRequest('/cust/foo')
        );

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
        $objectManager = $this->buildConfiguredObjectManager();
        $this->configureHandlerPath($objectManager);
        $response = $this->dispatch(
            $objectManager,
            $this->buildTestRequest('/cust/bar', 'POST')
        );

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
        $objectManager = $this->buildConfiguredObjectManager();
        $this->configureHandlerPath($objectManager);

        $response = $this->dispatch($objectManager, $this->buildTestRequest('/cust/foo', 'POST'));

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
        $objectManager = $this->buildConfiguredObjectManager();
        $this->configureHandlerPath($objectManager);
        $response = $this->dispatch($objectManager, $this->buildTestRequest('/cust/bar'));

        $this->assertSame(
            '{"error":"Not Found"}',
            (string)$response->getBody(),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    protected function configureHandlerPath(ObjectManagerInterface $objectManager): void
    {
        $this->configurePath(
            $objectManager,
            'cust',
            [
                "path"         => "cust",
                "read"         => "allow",
                "write"        => "allow",
                "handlerClass" => CustHandler::class,
            ]
        );
    }
}
