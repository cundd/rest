<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\ResponseFactory;
use Cundd\Rest\Tests\Functional\Fixtures\CustomHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Http\ResponseFactory as TYPO3ResponseFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class CustomHandlerTest extends AbstractIntegrationCase
{
    #[Test]
    public function getIndexTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/customhandler');

        $this->assertSame(
            '{"message":"GET Index"}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function getFooTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/customhandler/foo');

        $this->assertSame(
            '{"message":"GET Foo"}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function postBarTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch(
            $container,
            '/customhandler/bar',
            'POST'
        );

        $this->assertSame(
            '{"message":"POST Bar"}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function postFooShouldFailTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch(
            $container,
            '/customhandler/foo',
            'POST'
        );

        $this->assertSame(
            '{"error":"Not Found"}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function getBarShouldFailTest(): void
    {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch($container, '/customhandler/bar');

        $this->assertSame(
            '{"error":"Not Found"}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    #[Test]
    #[DataProvider('stringPathSegmentDataProvider')]
    public function stringPathSegmentTest(
        string $pathSegment,
        string $expected,
    ): void {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch(
            $container,
            '/customhandler/string-route/' . $pathSegment
        );

        $this->assertSame(
            '{"type":"string","value":"' . $expected . '"}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function stringPathSegmentDataProvider(): array
    {
        return array_map(
            fn (string $s) => [$s, $s],
            [
                'example-text',
                'example.text',
                'example_text',
                bin2hex(random_bytes(120)),
            ]
        );
    }

    #[Test]
    #[DataProvider('integerPathSegmentDataProvider')]
    public function integerPathSegmentTest(
        string $pathSegment,
        int $expected,
    ): void {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch(
            $container,
            '/customhandler/int-route/' . $pathSegment
        );

        $this->assertSame(
            '{"type":"int","value":' . $expected . '}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return list<array{0:string,1:int}>
     */
    public static function integerPathSegmentDataProvider(): array
    {
        $random = random_int(0, PHP_INT_MAX - 1);

        return [
            ['1', 1],
            ['0', 0],
            ['10', 10],
            [(string) $random, $random],
            [(string) PHP_INT_MAX, PHP_INT_MAX],

            // TODO: Add support for negative numbers
            // ['-1', -1],
            // [(string) PHP_INT_MIN, PHP_INT_MIN],
        ];
    }

    #[Test]
    #[DataProvider('floatPathSegmentDataProvider')]
    public function floatPathSegmentTest(
        string $pathSegment,
        float $expected,
    ): void {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch(
            $container,
            '/customhandler/float-route/' . $pathSegment
        );

        $this->assertSame(
            '{"type":"float","value":' . $expected . '}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return list<array{0:string,1:float}>
     */
    public static function floatPathSegmentDataProvider(): array
    {
        return [
            // TODO: Also accept integers?
            ['10.2', 10.2],
            ['0.0', 0.0],
            ['38920.02761', 38920.02761],

            // TODO: Add support for negative numbers
            // ['-1.0', -1.0],
        ];
    }

    #[Test]
    #[DataProvider('booleanPathSegmentDataProvider')]
    public function booleanPathSegmentTest(
        string $pathSegment,
        bool $expected,
    ): void {
        $container = $this->getContainer();
        $this->configureHandlerPath($container);
        $response = $this->buildRequestAndDispatch(
            $container,
            '/customhandler/boolean-route/' . $pathSegment
        );

        $this->assertSame(
            '{"type":"bool","value":' . ($expected ? 'true' : 'false') . '}',
            (string) $response->getBody(),
            sprintf(
                'Response "%s" was not expected',
                (string) $response->getBody()
            )
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return list<array{0:string,1:bool}>
     */
    public static function booleanPathSegmentDataProvider(): array
    {
        return [
            ['1', true],
            ['true', true],
            ['on', true],
            ['yes', true],

            ['0', false],
            ['false', false],
            ['off', false],
            ['no', false],
        ];
    }

    /**
     * @param ContainerInterface|Container $objectManager
     */
    protected function configureHandlerPath(
        ContainerInterface $objectManager,
    ): void {
        $typo3ResponseFactory = GeneralUtility::makeInstance(
            TYPO3ResponseFactory::class
        );
        assert($objectManager instanceof Container);
        $responseFactory = new ResponseFactory($typo3ResponseFactory);
        $objectManager->set(
            CustomHandler::class,
            new CustomHandler(new ResponseFactory($typo3ResponseFactory))
        );
        $this->configurePath(
            $objectManager,
            'customhandler',
            [
                'path'         => 'customhandler',
                'read'         => 'allow',
                'write'        => 'allow',
                'handlerClass' => CustomHandler::class,
            ]
        );
    }
}
