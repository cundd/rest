<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Tests\Functional\Fixtures\CustHandler;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;

class CustomHandlerTest extends AbstractIntegrationCase
{
    /**
     * @test
     */
    public function getIndexTest()
    {
        $objectManager = $this->getContainer();
        $this->configureHandlerPath($objectManager);
        $response = $this->dispatch($objectManager, $this->buildTestRequest('/cust'));

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
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->dispatch($container, $this->buildTestRequest('/cust/foo'));

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
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->dispatch($container, $this->buildTestRequest('/cust/bar', 'POST'));

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
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->dispatch($container, $this->buildTestRequest('/cust/foo', 'POST'));

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
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->dispatch($container, $this->buildTestRequest('/cust/bar'));

        $this->assertSame(
            '{"error":"Not Found"}',
            (string)$response->getBody(),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @param ContainerInterface|Container $objectManager
     * @return void
     */
    protected function configureHandlerPath(ContainerInterface $objectManager): void
    {
        $objectManager->set(CustHandler::class, new CustHandler());
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
