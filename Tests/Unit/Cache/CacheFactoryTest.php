<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Cache;

use Cundd\Rest\Cache\Cache;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\RequestBuilderTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Argument\Token\TypeToken;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;

class CacheFactoryTest extends TestCase
{
    use ProphecyTrait;
    use RequestBuilderTrait;

    private CacheFactory $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = new CacheFactory();
    }

    protected function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    #[DataProvider('buildCacheDataProvider')]
    public function buildCacheCheckExpiresHeaderLifetime(
        ?int $cacheLifetime,
        ?int $expiresHeaderLifetime,
        ?int $resourceTypeExpiresHeaderLifetime,
        int $resourceTypeCacheLifetime,
        int $_,
        int $expectedExpiresHeaderLifetime,
    ): void {
        $cache = $this->fixture->buildCache(
            new ResourceType(''),
            $this->getConfigurationProvider(
                $cacheLifetime,
                $expiresHeaderLifetime,
                $resourceTypeCacheLifetime,
                $resourceTypeExpiresHeaderLifetime
            ),
            $this->getObjectManager()
        );
        $this->assertEquals($expectedExpiresHeaderLifetime, $cache->getExpiresHeaderLifetime());
    }

    #[Test]
    #[DataProvider('buildCacheDataProvider')]
    public function buildCacheCheckLifetime(
        ?int $cacheLifetime,
        ?int $expiresHeaderLifetime,
        ?int $_,
        int $resourceTypeCacheLifetime,
        int $expectedCacheLifetime,
        mixed $_2,
    ): void {
        $cache = $this->fixture->buildCache(
            new ResourceType(''),
            $this->getConfigurationProvider(
                $cacheLifetime,
                $expiresHeaderLifetime,
                $resourceTypeCacheLifetime,
                $expiresHeaderLifetime
            ),
            $this->getObjectManager()
        );
        $this->assertEquals($expectedCacheLifetime, $cache->getCacheLifetime());
    }

    /**
     * @return array<int,list<int|null>>
     */
    public static function buildCacheDataProvider(): array
    {
        return [
            [10, 20, null, -1, 10, 20],
            [10, null, null, -1, 10, 10],
            [20, null, null, -1, 20, 20],
            [10, 20, null, 30, 30, 20],
            [null, null, null, 30, 30, 30],
            [null, null, null, -1, -1, -1],
            [10, 20, 30, -1, 10, 30],
            [10, null, 30, -1, 10, 30],
        ];
    }

    private function getObjectManager(): ObjectManagerInterface
    {
        $responseFactory = $this->prophesize(ResponseFactoryInterface::class)->reveal();

        $objectManager = $this->prophesize(ObjectManager::class);
        /** @var string $argumentType */
        $argumentType = Argument::type('string');
        $objectManager->get($argumentType)->willReturn(new Cache($responseFactory));

        return $objectManager->reveal();
    }

    private function getConfigurationProvider(
        ?int $cacheLifetime,
        ?int $expiresHeaderLifetime,
        int $resourceTypeCacheLifetime,
        ?int $resourceTypeExpiresHeaderLifetime,
    ): ConfigurationProviderInterface {
        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class);

        /** @var string $typeToken */
        $typeToken = Argument::type('string');
        $configurationProvider->getSetting($typeToken)->will(
            function ($args) use ($expiresHeaderLifetime, $cacheLifetime) {
                if (isset($args[0])) {
                    if ('cacheLifetime' === $args[0]) {
                        return $cacheLifetime;
                    }

                    if ('expiresHeaderLifetime' === $args[0]) {
                        return $expiresHeaderLifetime;
                    }
                }

                return null;
            }
        );

        /** @var \Cundd\Rest\Request\ResourceType|TypeToken $resourceType */
        $resourceType = Argument::type(ResourceType::class);
        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $configurationProvider->getResourceConfiguration($resourceType);
        $methodProphecy->will(
            function () use ($resourceTypeCacheLifetime, $resourceTypeExpiresHeaderLifetime) {
                return new ResourceConfiguration(
                    new ResourceType(''),
                    Access::Allowed,
                    Access::Denied,
                    $resourceTypeCacheLifetime,
                    '',
                    '',
                    [],
                    $resourceTypeExpiresHeaderLifetime ?? -1
                );
            }
        );

        return $configurationProvider->reveal();
    }
}
