<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\AbstractConfigurationProvider;
use Cundd\Rest\Configuration\AccessRequirement;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Request\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ValueError;

/**
 * @phpstan-import-type RawConfiguration from ConfigurationProviderInterface
 * @phpstan-import-type Settings from ConfigurationProviderInterface
 */
abstract class AbstractConfigurationProviderCase extends TestCase
{
    use ProphecyTrait;

    protected AbstractConfigurationProvider $fixture;

    public function setUp(): void
    {
        parent::setUp();

        /** @var class-string $fakeClass1 */
        $fakeClass1 = 'SomeClass2'; // @phpstan-ignore varTag.nativeType

        /** @var class-string $fakeClass2 */
        $fakeClass2 = 'SomeClass3'; // @phpstan-ignore varTag.nativeType

        $settings = [
            'paths' => [
                'all' => [
                    'path'         => 'all',
                    'read'         => 'allow',
                    'write'        => 'deny',
                    'handlerClass' => CrudHandler::class,
                ],
                'my_ext-my_model' => [
                    'path'         => 'my_ext-my_model',
                    'read'         => 'allow',
                    'write'        => 'allow',
                    'handlerClass' => $fakeClass1,
                ],
                'my_secondext-*' => [
                    'path'         => 'my_secondext-*',
                    'read'         => 'deny',
                    'write'        => 'allow',
                    'handlerClass' => $fakeClass2,
                ],
                'vendor-my_third_ext-model' => [
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
                'vendor-my_fourth_ext-model.' => [
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
            ],
        ];
        $this->fixture = $this->getConfigurationProviderToTest($settings);
    }

    /**
     * @param Settings $settings
     */
    abstract public function getConfigurationProviderToTest(
        array $settings,
    ): AbstractConfigurationProvider;

    #[Test]
    public function getConfiguredResourceTypesTest(): void
    {
        $fixture = $this->getConfigurationProviderToTest(
            [
                'paths' => [
                    'my_protectedext' => [
                        'path'  => 'my_protectedext-*',
                        'read'  => 'allow',
                        'write' => 'require',
                    ],
                    'vendor-my_ext-my_model' => [
                        'read'  => 'require',
                        'write' => 'deny',
                    ],
                    'vendor-my_other_ext-my_model' => [
                        'path'          => 'vendor-my_other_ext-my_model',
                        'cacheLifeTime' => 2,
                    ],
                    'vendor-my_other_ext-my_model2' => [
                        'path'          => 'vendor-my_other_ext-my_model2',
                        'cacheLifetime' => 3,
                        'cacheLifeTime' => 200, // the key "cacheLifetime" must have precedence
                    ],
                ],
            ]
        );

        $resourceTypeConfigurations = $fixture->getConfiguredResources();
        $this->assertCount(4, $resourceTypeConfigurations);
        array_map(
            function ($c) {
                $this->assertInstanceOf(ResourceConfiguration::class, $c);
            },
            $resourceTypeConfigurations
        );

        $resourceConfiguration1 = $resourceTypeConfigurations['my_protectedext-*'];
        $this->assertSame('my_protectedext-*', (string) $resourceConfiguration1->resourceType);
        $this->assertTrue(AccessRequirement::Allowed === $resourceConfiguration1->readAccess);
        $this->assertTrue(AccessRequirement::RequireLogin === $resourceConfiguration1->writeAccess);
        $this->assertSame(-1, $resourceConfiguration1->cacheLifetime);

        $resourceConfiguration2 = $resourceTypeConfigurations['vendor-my_ext-my_model'];
        $this->assertSame('vendor-my_ext-my_model', (string) $resourceConfiguration2->resourceType);
        $this->assertTrue(AccessRequirement::RequireLogin === $resourceConfiguration2->readAccess);
        $this->assertTrue(AccessRequirement::Denied === $resourceConfiguration2->writeAccess);
        $this->assertSame(-1, $resourceConfiguration2->cacheLifetime);

        $resourceConfiguration3 = $resourceTypeConfigurations['vendor-my_other_ext-my_model'];
        $this->assertSame('vendor-my_other_ext-my_model', (string) $resourceConfiguration3->resourceType);
        $this->assertTrue(AccessRequirement::Denied === $resourceConfiguration3->readAccess);
        $this->assertTrue(AccessRequirement::Denied === $resourceConfiguration3->writeAccess);
        $this->assertSame(2, $resourceConfiguration3->cacheLifetime);

        $resourceConfiguration4 = $resourceTypeConfigurations['vendor-my_other_ext-my_model2'];
        $this->assertSame('vendor-my_other_ext-my_model2', (string) $resourceConfiguration4->resourceType);
        $this->assertTrue(AccessRequirement::Denied === $resourceConfiguration4->readAccess);
        $this->assertTrue(AccessRequirement::Denied === $resourceConfiguration4->writeAccess);
        $this->assertSame(3, $resourceConfiguration4->cacheLifetime);
    }

    #[Test]
    public function getConfiguredResourceTypesInvalidReadTest(): void
    {
        $this->expectException(ValueError::class);
        $fixture = $this->getConfigurationProviderToTest(
            // @phpstan-ignore argument.type
            ['paths' => ['my_protectedext' => ['read' => 'invalid']]]
        );
        $fixture->getConfiguredResources();
    }

    #[Test]
    public function getConfiguredResourceTypesInvalidWriteTest(): void
    {
        $this->expectException(ValueError::class);

        $fixture = $this->getConfigurationProviderToTest(
            // @phpstan-ignore argument.type
            ['paths' => ['my_protectedext' => ['write' => 'invalid']]]
        );
        $fixture->getConfiguredResources();
    }

    #[Test]
    public function getDefaultConfigurationForPathTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_ext-my_default_model'));

        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('all', (string) $configuration->resourceType);
        $this->assertTrue(AccessRequirement::Allowed === $configuration->readAccess);
        $this->assertTrue(AccessRequirement::Denied === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithoutWildcardTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_ext-my_model'));
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('my_ext-my_model', (string) $configuration->resourceType);
        $this->assertTrue(AccessRequirement::Allowed === $configuration->readAccess);
        $this->assertTrue(AccessRequirement::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithoutExplicitPathConfigurationTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('vendor-my_third_ext-model'));
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('vendor-my_third_ext-model', (string) $configuration->resourceType);
        $this->assertTrue(AccessRequirement::Denied === $configuration->readAccess);
        $this->assertTrue(AccessRequirement::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithoutExplicitPathConfigurationWithDotTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(
            new ResourceType('vendor-my_fourth_ext-model')
        );
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('vendor-my_fourth_ext-model', (string) $configuration->resourceType);
        $this->assertTrue(AccessRequirement::Denied === $configuration->readAccess);
        $this->assertTrue(AccessRequirement::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithWildcardTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_secondext-my_model'));
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('my_secondext-*', (string) $configuration->resourceType);
        $this->assertTrue(AccessRequirement::Denied === $configuration->readAccess);
        $this->assertTrue(AccessRequirement::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfiguredHandlersTest(): void
    {
        $handlerConfigurations = $this->fixture->getConfiguredResources();
        $this->assertCount(5, $handlerConfigurations);
        array_map(
            fn ($c) => $this->assertInstanceOf(ResourceConfiguration::class, $c),
            $handlerConfigurations
        );

        $handlerConfiguration1 = $handlerConfigurations['all'];
        $this->assertSame('all', (string) $handlerConfiguration1->resourceType);
        $this->assertSame(CrudHandler::class, $handlerConfiguration1->handlerClass);

        $handlerConfiguration2 = $handlerConfigurations['my_ext-my_model'];
        $this->assertSame('my_ext-my_model', (string) $handlerConfiguration2->resourceType);
        $this->assertSame('SomeClass2', $handlerConfiguration2->handlerClass);

        $handlerConfiguration3 = $handlerConfigurations['vendor-my_third_ext-model'];
        $this->assertSame('vendor-my_third_ext-model', (string) $handlerConfiguration3->resourceType);
        $this->assertSame('', $handlerConfiguration3->handlerClass);
    }

    /**
     * @param Settings $settings
     */
    #[Test]
    #[DataProvider('cacheConfigurationFromResourceConfigurationProvider')]
    public function getCacheConfigurationFromResourceConfigurationTest(
        array $settings,
        int $cacheLifetime,
        int $expiresHeaderLifetime,
    ): void {
        $fixture = $this->getConfigurationProviderToTest($settings);
        $configuration = $fixture->getResourceConfiguration(
            new ResourceType('foo-bar')
        );

        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame($cacheLifetime, $configuration->cacheLifetime);
        $this->assertSame(
            $expiresHeaderLifetime,
            $configuration->expiresHeaderLifetime
        );
    }

    /**
     * @return array<non-empty-string,array{0:Settings,1:int,2:int}>
     */
    public static function cacheConfigurationFromResourceConfigurationProvider(): array
    {
        return [
            'no cache configuration' => [
                [
                    'paths' => [
                        'foo-bar' => [],
                    ],
                ],
                -1,
                -1,
            ],

            'cache lifetime configuration for Resource Type' => [
                [
                    'paths' => [
                        'foo-bar' => [
                            'cacheLifetime' => 200,
                        ],
                    ],
                ],
                200,
                -1,
            ],

            'expires header lifetime configuration for Resource Type' => [
                [
                    'paths' => [
                        'foo-bar' => [
                            'expiresHeaderLifetime' => 60 * 60 * 24 * 30,
                        ],
                    ],
                ],
                -1,
                60 * 60 * 24 * 30,
            ],
        ];
    }
}
