<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Tests\Functional\Fixtures\CustHandler;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;

class CustomHandlerTest extends AbstractIntegrationCase
{
    #[Test]
    public function getIndexTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/cust');

        $this->assertSame(
            '{"message":"GET Index"}',
            (string) $response->getBody(),
            sprintf('Response "%s" was not expected', (string) $response->getBody())
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function getFooTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/cust/foo');

        $this->assertSame(
            '{"message":"GET Foo"}',
            (string) $response->getBody(),
            sprintf('Response "%s" was not expected', (string) $response->getBody())
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function postBarTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/cust/bar', 'POST');

        $this->assertSame(
            '{"message":"POST Bar"}',
            (string) $response->getBody(),
            sprintf('Response "%s" was not expected', (string) $response->getBody())
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function postFooShouldFailTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/cust/foo', 'POST');

        $this->assertSame(
            '{"error":"Not Found"}',
            (string) $response->getBody(),
            sprintf('Response "%s" was not expected', (string) $response->getBody())
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function getBarShouldFailTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/cust/bar');

        $this->assertSame(
            '{"error":"Not Found"}',
            (string) $response->getBody(),
            sprintf('Response "%s" was not expected', (string) $response->getBody())
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @param ContainerInterface|Container $objectManager
     */
    protected function configureHandlerPath(ContainerInterface $objectManager): void
    {
        assert($objectManager instanceof Container);
        $objectManager->set(CustHandler::class, new CustHandler());
        $this->configurePath(
            $objectManager,
            'cust',
            [
                'path'         => 'cust',
                'read'         => 'allow',
                'write'        => 'allow',
                'handlerClass' => CustHandler::class,
            ]
        );
    }
}
