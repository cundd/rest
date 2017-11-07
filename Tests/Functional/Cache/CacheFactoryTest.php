<?php

namespace Cundd\Rest\Tests\Functional\Cache;


use Cundd\Rest\Cache\Cache;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CacheFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CacheFactory
     */
    private $fixture;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new CacheFactory();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider buildCacheDataProvider
     * @param int      $cacheLifeTime
     * @param int|null $expiresHeaderLifeTime
     * @param int      $expectedCacheLifeTime
     * @param int      $expectedExpiresHeaderLifeTime
     */
    public function buildCacheTest(
        $cacheLifeTime,
        $expiresHeaderLifeTime,
        $expectedCacheLifeTime,
        $expectedExpiresHeaderLifeTime
    ) {
        $cache = $this->fixture->buildCache(
            $this->getConfigurationProvider($cacheLifeTime, $expiresHeaderLifeTime),
            $this->getObjectManager()
        );
        $this->assertEquals($expectedCacheLifeTime, $cache->getCacheLifeTime());
        $this->assertEquals($expectedExpiresHeaderLifeTime, $cache->getExpiresHeaderLifeTime());
    }

    public function buildCacheDataProvider()
    {
        return [
            [10, 20, 10, 20],
            [10, null, 10, 10],
            [20, null, 20, 20],
            [null, null, -1, -1],
        ];
    }

    /**
     * @return ObjectManagerInterface
     */
    private function getObjectManager()
    {
        /** @var ObjectManager|ObjectProphecy $responseFactory */
        $responseFactory = $this->prophesize(ResponseFactoryInterface::class);

        /** @var ObjectManager|ObjectProphecy $objectManager */
        $objectManager = $this->prophesize(ObjectManager::class);
        $objectManager->get(Argument::type('string'))->willReturn(new Cache($responseFactory->reveal()));

        return $objectManager->reveal();
    }

    /**
     * @param $cacheLifeTime
     * @param $expiresHeaderLifeTime
     * @return ConfigurationProviderInterface
     */
    private function getConfigurationProvider($cacheLifeTime, $expiresHeaderLifeTime)
    {
        /** @var ConfigurationProviderInterface|ObjectProphecy $configurationProvider */
        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class);

        $configurationProvider->getSetting(Argument::type('string'))->will(
            function ($args) use ($expiresHeaderLifeTime, $cacheLifeTime) {
                if (isset($args[0])) {
                    if ($args[0] === 'cacheLifeTime') {
                        return $cacheLifeTime;
                    }
                    if ($args[0] === 'expiresHeaderLifeTime') {
                        return $expiresHeaderLifeTime;
                    }
                }

                return null;
            }
        );

        return $configurationProvider->reveal();
    }
}
