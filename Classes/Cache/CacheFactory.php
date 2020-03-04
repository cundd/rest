<?php
declare(strict_types=1);

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
    ): CacheInterface {
        $cacheInstance = $this->getCacheInstance($configurationProvider, $objectManager);

        $cacheInstance->setCacheLifetime(
            $this->getCacheLifetime($configurationProvider, $resourceType)
        );
        $cacheInstance->setExpiresHeaderLifetime(
            $this->getExpiresHeaderLifetime($configurationProvider, $resourceType)
        );

        return $cacheInstance;
    }

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     * @param ObjectManager                  $objectManager
     * @return CacheInterface
     */
    private function getCacheInstance(
        ConfigurationProviderInterface $configurationProvider,
        ObjectManager $objectManager
    ): CacheInterface {
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
        $resourceConfiguration = $configurationProvider->getResourceConfiguration($resourceType);
        $expiresHeaderLifetime = $resourceConfiguration->getExpiresHeaderLifetime();
        if ($expiresHeaderLifetime > -1) {
            return $expiresHeaderLifetime;
        }

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
