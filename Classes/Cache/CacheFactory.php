<?php
declare(strict_types=1);

namespace Cundd\Rest\Cache;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\ObjectManager;

class CacheFactory
{
    /**
     * @param ConfigurationProviderInterface $configurationProvider
     * @param ObjectManager                  $objectManager
     * @return CacheInterface
     */
    public function buildCache(
        ConfigurationProviderInterface $configurationProvider,
        ObjectManager $objectManager
    ): CacheInterface {
        $cacheInstance = $this->getCacheInstance($configurationProvider, $objectManager);

        if ($cacheInstance instanceof CacheInterface) {
            $cacheInstance->setCacheLifeTime($this->getCacheLifeTime($configurationProvider));
            $cacheInstance->setExpiresHeaderLifeTime($this->getExpiresHeaderLifeTime($configurationProvider));
        }

        return $cacheInstance;
    }

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     * @param ObjectManager                  $objectManager
     * @return mixed
     */
    private function getCacheInstance(
        ConfigurationProviderInterface $configurationProvider,
        ObjectManager $objectManager
    ) {
        $cacheImplementation = $configurationProvider->getSetting('cacheClass');
        if ($cacheImplementation && $objectManager->isRegistered($cacheImplementation)) {
            return $objectManager->get($cacheImplementation);
        }

        return $objectManager->get(Cache::class);
    }

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     * @return int|mixed
     */
    private function getCacheLifeTime(ConfigurationProviderInterface $configurationProvider): int
    {
        $readCacheLifeTime = $configurationProvider->getSetting('cacheLifeTime');
        if ($readCacheLifeTime === null) {
            $readCacheLifeTime = -1;
        }

        return (int)$readCacheLifeTime;
    }

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     * @return int|mixed
     */
    private function getExpiresHeaderLifeTime(ConfigurationProviderInterface $configurationProvider): int
    {
        $expiresHeaderLifeTime = $configurationProvider->getSetting('expiresHeaderLifeTime');

        return ($expiresHeaderLifeTime !== null)
            ? intval($expiresHeaderLifeTime)
            : $this->getCacheLifeTime($configurationProvider);
    }
}
