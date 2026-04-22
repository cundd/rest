<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\AbstractConfigurationProvider;
use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Request\ResourceType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ValueError;

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
        $this->fixture = $this->getConfigurationProviderToTest();
        $this->fixture->setSettings($settings);
    }

    abstract public function getConfigurationProviderToTest(): AbstractConfigurationProvider;

    #[Test]
    public function getConfiguredResourceTypesTest(): void
    {
        $this->fixture->setSettings(
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

        $resourceTypeConfigurations = $this->fixture->getConfiguredResources();
        $this->assertCount(4, $resourceTypeConfigurations);
        array_map(
            function ($c) {
                $this->assertInstanceOf(ResourceConfiguration::class, $c);
            },
            $resourceTypeConfigurations
        );

        $resourceConfiguration1 = $resourceTypeConfigurations['my_protectedext-*'];
        $this->assertSame('my_protectedext-*', (string) $resourceConfiguration1->getResourceType());
        $this->assertTrue(Access::Allowed === $resourceConfiguration1->readAccess);
        $this->assertTrue(Access::RequireLogin === $resourceConfiguration1->writeAccess);
        $this->assertSame(-1, $resourceConfiguration1->getCacheLifetime());

        $resourceConfiguration2 = $resourceTypeConfigurations['vendor-my_ext-my_model'];
        $this->assertSame('vendor-my_ext-my_model', (string) $resourceConfiguration2->getResourceType());
        $this->assertTrue(Access::RequireLogin === $resourceConfiguration2->readAccess);
        $this->assertTrue(Access::Denied === $resourceConfiguration2->writeAccess);
        $this->assertSame(-1, $resourceConfiguration2->getCacheLifetime());

        $resourceConfiguration3 = $resourceTypeConfigurations['vendor-my_other_ext-my_model'];
        $this->assertSame('vendor-my_other_ext-my_model', (string) $resourceConfiguration3->getResourceType());
        $this->assertTrue(Access::Denied === $resourceConfiguration3->readAccess);
        $this->assertTrue(Access::Denied === $resourceConfiguration3->writeAccess);
        $this->assertSame(2, $resourceConfiguration3->getCacheLifetime());

        $resourceConfiguration4 = $resourceTypeConfigurations['vendor-my_other_ext-my_model2'];
        $this->assertSame('vendor-my_other_ext-my_model2', (string) $resourceConfiguration4->getResourceType());
        $this->assertTrue(Access::Denied === $resourceConfiguration4->readAccess);
        $this->assertTrue(Access::Denied === $resourceConfiguration4->writeAccess);
        $this->assertSame(3, $resourceConfiguration4->getCacheLifetime());
    }

    #[Test]
    public function getConfiguredResourceTypesInvalidReadTest(): void
    {
        $this->expectException(ValueError::class);
        $this->fixture->setSettings(['paths' => ['my_protectedext' => ['read' => 'invalid']]]); // @phpstan-ignore argument.type
        $this->fixture->getConfiguredResources();
    }

    #[Test]
    public function getConfiguredResourceTypesInvalidWriteTest(): void
    {
        $this->expectException(ValueError::class);
        $this->fixture->setSettings(['paths' => ['my_protectedext' => ['write' => 'invalid']]]); // @phpstan-ignore argument.type
        $this->fixture->getConfiguredResources();
    }

    #[Test]
    public function getDefaultConfigurationForPathTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_ext-my_default_model'));

        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('all', (string) $configuration->resourceType);
        $this->assertTrue(Access::Allowed === $configuration->readAccess);
        $this->assertTrue(Access::Denied === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithoutWildcardTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_ext-my_model'));
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('my_ext-my_model', (string) $configuration->resourceType);
        $this->assertTrue(Access::Allowed === $configuration->readAccess);
        $this->assertTrue(Access::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithoutExplicitPathConfigurationTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('vendor-my_third_ext-model'));
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('vendor-my_third_ext-model', (string) $configuration->resourceType);
        $this->assertTrue(Access::Denied === $configuration->readAccess);
        $this->assertTrue(Access::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithoutExplicitPathConfigurationWithDotTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(
            new ResourceType('vendor-my_fourth_ext-model')
        );
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('vendor-my_fourth_ext-model', (string) $configuration->resourceType);
        $this->assertTrue(Access::Denied === $configuration->readAccess);
        $this->assertTrue(Access::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfigurationForPathWithWildcardTest(): void
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_secondext-my_model'));
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertSame('my_secondext-*', (string) $configuration->resourceType);
        $this->assertTrue(Access::Denied === $configuration->readAccess);
        $this->assertTrue(Access::Allowed === $configuration->writeAccess);
    }

    #[Test]
    public function getConfiguredHandlersTest(): void
    {
        $handlerConfigurations = $this->fixture->getConfiguredResources();
        $this->assertCount(5, $handlerConfigurations);
        array_map(
            function ($c) {
                $this->assertInstanceOf(ResourceConfiguration::class, $c);
            },
            $handlerConfigurations
        );

        $handlerConfiguration1 = $handlerConfigurations['all'];
        $this->assertSame('all', (string) $handlerConfiguration1->getResourceType());
        $this->assertSame(CrudHandler::class, $handlerConfiguration1->getHandlerClass());

        $handlerConfiguration2 = $handlerConfigurations['my_ext-my_model'];
        $this->assertSame('my_ext-my_model', (string) $handlerConfiguration2->getResourceType());
        $this->assertSame('SomeClass2', $handlerConfiguration2->getHandlerClass());

        $handlerConfiguration3 = $handlerConfigurations['vendor-my_third_ext-model'];
        $this->assertSame('vendor-my_third_ext-model', (string) $handlerConfiguration3->getResourceType());
        $this->assertSame('', $handlerConfiguration3->getHandlerClass());
    }
}
