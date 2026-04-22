<?php

declare(strict_types=1);

namespace Cundd\Rest\Cache;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Request\ResourceType;

class CacheFactory
{
    /**
     * Return a Cache instance for the Resource Type
     */
    public function buildCache(
        ResourceType $resourceType,
        ConfigurationProviderInterface $configurationProvider,
        ObjectManager $objectManager,
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

    private function getCacheInstance(
        ConfigurationProviderInterface $configurationProvider,
        ObjectManager $objectManager,
    ): CacheInterface {
        $cacheImplementation = $configurationProvider->getSetting('cacheClass');
        if ($cacheImplementation && class_exists($cacheImplementation)) {
            return $objectManager->get($cacheImplementation); // @phpstan-ignore return.type
        }

        return $objectManager->get(Cache::class); // @phpstan-ignore return.type
    }

    private function getCacheLifetime(
        ConfigurationProviderInterface $configurationProvider,
        ResourceType $resourceType,
    ): int {
        $resourceConfiguration = $configurationProvider->getResourceConfiguration($resourceType);
        $cacheLifetime = $resourceConfiguration?->getCacheLifetime();
        if ($cacheLifetime > -1) {
            return $cacheLifetime;
        }

        $cacheLifetime = $configurationProvider->getSetting('cacheLifetime');
        if (null !== $cacheLifetime && is_numeric($cacheLifetime) && $cacheLifetime > -1) {
            return (int) $cacheLifetime;
        }

        $cacheLifetime = $configurationProvider->getSetting('cacheLifeTime');
        if (null !== $cacheLifetime && is_numeric($cacheLifetime) && $cacheLifetime > -1) {
            return (int) $cacheLifetime;
        }

        return -1;
    }

    private function getExpiresHeaderLifetime(
        ConfigurationProviderInterface $configurationProvider,
        ResourceType $resourceType,
    ): int {
        $resourceConfiguration = $configurationProvider->getResourceConfiguration($resourceType);
        $expiresHeaderLifetime = $resourceConfiguration->getExpiresHeaderLifetime();
        if ($expiresHeaderLifetime > -1) {
            return $expiresHeaderLifetime;
        }

        $expiresHeaderLifetime = $configurationProvider->getSetting('expiresHeaderLifetime');
        if (null !== $expiresHeaderLifetime) {
            return (int) $expiresHeaderLifetime;
        }

        $expiresHeaderLifetime = $configurationProvider->getSetting('expiresHeaderLifeTime');
        if (null !== $expiresHeaderLifetime) {
            return (int) $expiresHeaderLifetime;
        }

        return $this->getCacheLifetime($configurationProvider, $resourceType);
    }
}
