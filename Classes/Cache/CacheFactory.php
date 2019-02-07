<?php

namespace Cundd\Rest\Cache;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManager;

class CacheFactory
{
    /**
     * Return a Cache instance for the Resource Type
     *
     * @param ResourceType                   $resourceType
     * @param ConfigurationProviderInterface $configurationProvider
     * @param ObjectManager                  $objectManager
     * @return CacheInterface
     */
    public function buildCache(
        ResourceType $resourceType,
        ConfigurationProviderInterface $configurationProvider,
        ObjectManager $objectManager
    ) {
        $cacheInstance = $this->getCacheInstance($configurationProvider, $objectManager);

        if ($cacheInstance instanceof CacheInterface) {
            $cacheInstance->setCacheLifetime(
                $this->getCacheLifetime($configurationProvider, $resourceType)
            );
            $cacheInstance->setExpiresHeaderLifetime(
                $this->getExpiresHeaderLifetime($configurationProvider, $resourceType)
            );
        }

        return $cacheInstance;
    }

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     * @param ObjectManager                  $objectManager
     * @return CacheInterface|null
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
     * @param ResourceType                   $resourceType
     * @return int
     */
    private function getCacheLifetime(ConfigurationProviderInterface $configurationProvider, ResourceType $resourceType)
    {
        $resourceConfiguration = $configurationProvider->getResourceConfiguration($resourceType);
        $cacheLifetime = $resourceConfiguration->getCacheLifetime();
        if ($cacheLifetime > -1) {
            return $cacheLifetime;
        }

        $cacheLifetime = $configurationProvider->getSetting('cacheLifeTime');
        if ($cacheLifetime !== null) {
            return (int)$cacheLifetime;
        }

        $cacheLifetime = $configurationProvider->getSetting('cacheLifetime');
        if ($cacheLifetime !== null) {
            return (int)$cacheLifetime;
        }

        return -1;
    }

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     * @param ResourceType                   $resourceType
     * @return int
     */
    private function getExpiresHeaderLifetime(
        ConfigurationProviderInterface $configurationProvider,
        ResourceType $resourceType
    ) {
        $expiresHeaderLifetime = $configurationProvider->getSetting('expiresHeaderLifetime');
        if ($expiresHeaderLifetime !== null) {
            return (int)$expiresHeaderLifetime;
        }

        $expiresHeaderLifetime = $configurationProvider->getSetting('expiresHeaderLifeTime');
        if ($expiresHeaderLifetime !== null) {
            return (int)$expiresHeaderLifetime;
        }

        return $this->getCacheLifetime($configurationProvider, $resourceType);
    }
}
