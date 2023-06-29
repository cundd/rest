<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Cache;

use Cundd\Rest\Cache\Cache;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\RequestBuilderTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Argument\Token\TypeToken;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

class CacheFactoryTest extends TestCase
{
    use ProphecyTrait;
    use RequestBuilderTrait;

    /**
     * @var CacheFactory
     */
    private $fixture;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = new CacheFactory();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider buildCacheDataProvider
     * @param int|null $cacheLifetime
     * @param int|null $expiresHeaderLifetime
     * @param int|null $resourceTypeExpiresHeaderLifetime
     * @param int      $resourceTypeCacheLifetime
     * @param int      $_
     * @param int      $expectedExpiresHeaderLifetime
     */
    public function buildCacheCheckExpiresHeaderLifetimeTest(
        $cacheLifetime,
        $expiresHeaderLifetime,
        $resourceTypeExpiresHeaderLifetime,
        $resourceTypeCacheLifetime,
        /** @noinspection PhpUnusedParameterInspection */ $_,
        $expectedExpiresHeaderLifetime
    ) {
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

    /**
     * @test
     * @dataProvider buildCacheDataProvider
     * @param int|null $cacheLifetime
     * @param int|null $expiresHeaderLifetime
     * @param int|null $_
     * @param int      $resourceTypeCacheLifetime
     * @param int      $expectedCacheLifetime
     * @noinspection PhpUnusedParameterInspection
     */
    public function buildCacheCheckLifetimeTest(
        $cacheLifetime,
        $expiresHeaderLifetime,
        /** @noinspection PhpUnusedParameterInspection */ $_,
        $resourceTypeCacheLifetime,
        $expectedCacheLifetime
    ) {
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

    public function buildCacheDataProvider()
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

    /**
     * @return ObjectManagerInterface|ObjectManager
     */
    private function getObjectManager()
    {
        /** @var ResponseFactoryInterface|ObjectProphecy $responseFactory */
        $responseFactory = $this->prophesize(ResponseFactoryInterface::class)->reveal();

        /** @var ObjectManager|ObjectProphecy $objectManager */
        $objectManager = $this->prophesize(ObjectManager::class);
        $objectManager->get(Argument::type('string'))->willReturn(new Cache($responseFactory));

        return $objectManager->reveal();
    }

    /**
     * @param int $cacheLifetime
     * @param int $expiresHeaderLifetime
     * @param int $resourceTypeCacheLifetime
     * @param int $resourceTypeExpiresHeaderLifetime
     * @return ConfigurationProviderInterface
     */
    private function getConfigurationProvider(
        $cacheLifetime,
        $expiresHeaderLifetime,
        $resourceTypeCacheLifetime,
        $resourceTypeExpiresHeaderLifetime
    ) {
        /** @var ConfigurationProviderInterface|ObjectProphecy $configurationProvider */
        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class);

        /** @var string $typeToken */
        $typeToken = Argument::type('string');
        $configurationProvider->getSetting($typeToken)->will(
            function ($args) use ($expiresHeaderLifetime, $cacheLifetime) {
                if (isset($args[0])) {
                    if ($args[0] === 'cacheLifetime') {
                        return $cacheLifetime;
                    }

                    if ($args[0] === 'expiresHeaderLifetime') {
                        return $expiresHeaderLifetime;
                    }
                }

                return null;
            }
        );

        /** @var ResourceType|TypeToken $resourceType */
        $resourceType = Argument::type(ResourceType::class);
        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $configurationProvider->getResourceConfiguration($resourceType);
        $methodProphecy->will(
            function () use ($resourceTypeCacheLifetime, $resourceTypeExpiresHeaderLifetime) {
                return new ResourceConfiguration(
                    new ResourceType(''),
                    Access::allowed(),
                    Access::denied(),
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
