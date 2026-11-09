<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Cache;

use Cundd\Rest\Cache\Cache;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Configuration\AccessRequirement;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\Fixtures\CacheTestConfiguration;
use Cundd\Rest\Tests\RequestBuilderTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Argument\Token\TypeToken;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

final class CacheFactoryTest extends TestCase
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
        CacheTestConfiguration $configuration,
    ): void {
        $cache = $this->fixture->buildCache(
            new ResourceType('some-resource-type'),
            $this->getConfigurationProvider(
                $configuration->globalCacheLifetime,
                $configuration->globalExpiresHeaderLifetime,
                $configuration->resourceTypeCacheLifetime,
                $configuration->resourceTypeExpiresHeaderLifetime
            ),
            $this->getObjectManager()
        );
        $this->assertEquals(
            $configuration->expectedExpiresHeaderLifetime,
            $cache->getExpiresHeaderLifetime()
        );
    }

    #[Test]
    #[DataProvider('buildCacheDataProvider')]
    public function buildCacheCheckLifetime(
        CacheTestConfiguration $configuration,
    ): void {
        $cache = $this->fixture->buildCache(
            new ResourceType('some-resource-type'),
            $this->getConfigurationProvider(
                $configuration->globalCacheLifetime,
                $configuration->globalExpiresHeaderLifetime,
                $configuration->resourceTypeCacheLifetime,
                $configuration->resourceTypeExpiresHeaderLifetime
            ),
            $this->getObjectManager()
        );
        $this->assertEquals(
            $configuration->expectedCacheLifetime,
            $cache->getCacheLifetime()
        );
    }

    /**
     * @return list<array{CacheTestConfiguration}>
     */
    public static function buildCacheDataProvider(): array
    {
        return [
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: -1,
                resourceTypeExpiresHeaderLifetime: null,
                globalCacheLifetime: 10,
                globalExpiresHeaderLifetime: 20,
                expectedCacheLifetime: 10,
                expectedExpiresHeaderLifetime: 20,
            )],
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: -1,
                resourceTypeExpiresHeaderLifetime: null,
                globalCacheLifetime: 10,
                globalExpiresHeaderLifetime: null,
                expectedCacheLifetime: 10,
                expectedExpiresHeaderLifetime: 10,
            )],
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: -1,
                resourceTypeExpiresHeaderLifetime: null,
                globalCacheLifetime: 20,
                globalExpiresHeaderLifetime: null,
                expectedCacheLifetime: 20,
                expectedExpiresHeaderLifetime: 20,
            )],
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: 30,
                resourceTypeExpiresHeaderLifetime: null,
                globalCacheLifetime: 10,
                globalExpiresHeaderLifetime: 20,
                expectedCacheLifetime: 30,
                expectedExpiresHeaderLifetime: 20,
            )],
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: 30,
                resourceTypeExpiresHeaderLifetime: null,
                globalCacheLifetime: null,
                globalExpiresHeaderLifetime: null,
                expectedCacheLifetime: 30,
                expectedExpiresHeaderLifetime: 30,
            )],
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: -1,
                resourceTypeExpiresHeaderLifetime: null,
                globalCacheLifetime: null,
                globalExpiresHeaderLifetime: null,
                expectedCacheLifetime: -1,
                expectedExpiresHeaderLifetime: -1,
            )],
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: -1,
                resourceTypeExpiresHeaderLifetime: 30,
                globalCacheLifetime: 10,
                globalExpiresHeaderLifetime: 20,
                expectedCacheLifetime: 10,
                expectedExpiresHeaderLifetime: 30,
            )],
            [new CacheTestConfiguration(
                resourceTypeCacheLifetime: -1,
                resourceTypeExpiresHeaderLifetime: 30,
                globalCacheLifetime: 10,
                globalExpiresHeaderLifetime: null,
                expectedCacheLifetime: 10,
                expectedExpiresHeaderLifetime: 30,
            )],
        ];
    }

    private function getObjectManager(): ObjectManagerInterface
    {
        $responseFactory = $this->prophesize(ResponseFactoryInterface::class)
            ->reveal();
        $cacheFrontend = $this->prophesize(FrontendInterface::class)->reveal();

        $objectManager = $this->prophesize(ObjectManager::class);
        /** @var string $argumentType */
        $argumentType = Argument::type('string');
        $objectManager->get($argumentType)->willReturn(new Cache(
            $responseFactory,
            $cacheFrontend
        ));

        return $objectManager->reveal();
    }

    private function getConfigurationProvider(
        ?int $cacheLifetime,
        ?int $expiresHeaderLifetime,
        int $resourceTypeCacheLifetime,
        ?int $resourceTypeExpiresHeaderLifetime,
    ): ConfigurationProviderInterface {
        $configurationProvider = $this->prophesize(
            ConfigurationProviderInterface::class
        );

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

        /** @var ResourceType|TypeToken $resourceType */
        $resourceType = Argument::type(ResourceType::class);
        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $configurationProvider
            ->getResourceConfiguration($resourceType);
        $methodProphecy->will(fn () => new ResourceConfiguration(
            new ResourceType('some-resource-type'),
            AccessRequirement::Allowed,
            AccessRequirement::Denied,
            $resourceTypeCacheLifetime,
            '',
            '',
            [],
            $resourceTypeExpiresHeaderLifetime ?? -1
        ));

        return $configurationProvider->reveal();
    }
}
