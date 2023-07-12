<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\Access\Exception\InvalidConfigurationException as InvalidAccessConfigurationException;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Exception\InvalidConfigurationException;
use Cundd\Rest\Exception\InvalidResourceTypeException;
use Cundd\Rest\SingletonInterface;

use function is_numeric;

/**
 * Abstract Configuration Provider
 */
abstract class AbstractConfigurationProvider implements SingletonInterface, ConfigurationProviderInterface
{
    /**
     * Settings read from the TypoScript
     *
     * @var array
     */
    protected $settings = null;

    /**
     * Return the setting with the given key
     *
     * @param string $keyPath
     * @param mixed  $defaultValue
     * @return mixed
     */
    public function getSetting(string $keyPath, $defaultValue = null)
    {
        $matchingSetting = $this->getSettings();

        $keyPathParts = explode('.', $keyPath);
        foreach ($keyPathParts as $key) {
            if (is_array($matchingSetting)) {
                if (isset($matchingSetting[$key . '.'])) {
                    $matchingSetting = $matchingSetting[$key . '.'];
                } elseif (isset($matchingSetting[$key])) {
                    $matchingSetting = $matchingSetting[$key];
                } else {
                    $matchingSetting = null;
                }
            } else {
                $matchingSetting = null;
            }
        }
        if (is_null($matchingSetting) && !is_null($defaultValue)) {
            return $defaultValue;
        }

        return $matchingSetting;
    }

    /**
     * Return the settings read from the TypoScript
     *
     * @return array
     */
    public function getSettings(): array
    {
        if ($this->settings === null) {
            throw new InvalidConfigurationException('No settings provided');
        }

        return $this->settings;
    }

    /**
     * Overwrite the settings
     *
     * @param array $settings
     * @internal
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Return the configuration matching the given resource type
     *
     * @param ResourceType $resourceType
     * @return ResourceConfiguration|null
     */
    public function getResourceConfiguration(ResourceType $resourceType): ?ResourceConfiguration
    {
        $configuredPaths = $this->getConfiguredResources();
        $matchingConfiguration = null;
        $resourceTypeString = Utility::normalizeResourceType($resourceType);

        if (!$resourceTypeString) {
            throw new InvalidResourceTypeException(
                sprintf(
                    'Invalid normalized Resource Type "%s"',
                    $resourceTypeString
                )
            );
        }

        foreach ($configuredPaths as $configuration) {
            $currentResourceTypeString = (string)$configuration->getResourceType();
            if ('all' === $currentResourceTypeString && !$matchingConfiguration) {
                $matchingConfiguration = $configuration;
            } elseif ($this->checkIfPatternMatchesResourceType($currentResourceTypeString, $resourceTypeString)) {
                $matchingConfiguration = $configuration;
            }
        }

        if (null === $matchingConfiguration) {
            throw new InvalidAccessConfigurationException(
                'No matching Resource Configuration found and "all" is not configured'
            );
        }

        return $matchingConfiguration;
    }

    /**
     * Return the paths configured in the settings
     *
     * @return ResourceConfiguration[]
     */
    public function getConfiguredResources(): array
    {
        $configurationCollection = [];
        foreach ($this->getRawConfiguredResourceTypes() as $path => $configuration) {
            [$configuration, $normalizeResourceType] = $this->preparePath($configuration, $path);

            $readAccess = isset($configuration[self::ACCESS_METHOD_READ])
                ? new Access($configuration[self::ACCESS_METHOD_READ])
                : Access::denied();
            $writeAccess = isset($configuration[self::ACCESS_METHOD_WRITE])
                ? new Access($configuration[self::ACCESS_METHOD_WRITE])
                : Access::denied();

            if (isset($configuration['className'])) {
                throw new InvalidConfigurationException('Unsupported configuration key "className"');
            }

            $resourceType = new ResourceType($normalizeResourceType);
            $cacheLifetime = $this->detectCacheLifetimeConfiguration($configuration);
            $expiresHeaderLifetime = $this->detectExpiresHeaderLifetimeConfiguration($configuration);

            $configurationCollection[$normalizeResourceType] = new ResourceConfiguration(
                $resourceType,
                $readAccess,
                $writeAccess,
                $cacheLifetime,
                $configuration['handlerClass'] ?? '',
                $configuration['dataProviderClass'] ?? '',
                $this->getAliasesForResourceType($resourceType),
                $expiresHeaderLifetime
            );
        }

        return $configurationCollection;
    }

    /**
     * Check if the given pattern matches the resource type
     *
     * @param string $pattern
     * @param string $resourceTypeString
     * @return bool
     */
    private function checkIfPatternMatchesResourceType(string $pattern, string $resourceTypeString): bool
    {
        $currentPathPattern = str_replace(
            '*',
            '\w*',
            str_replace('?', '\w', $pattern)
        );

        return 0 !== preg_match("!^$currentPathPattern$!", $resourceTypeString);
    }

    /**
     * Fetch aliases for the given Resource Type
     *
     * @param ResourceType $resourceType
     * @return string[]
     */
    private function getAliasesForResourceType(ResourceType $resourceType): array
    {
        $resourceTypeString = (string)$resourceType;

        return array_keys(
            array_filter(
                $this->getSetting('aliases', []),
                function ($alias) use ($resourceTypeString) {
                    // Return if the given Resource Type would handle this alias
                    return $this->checkIfPatternMatchesResourceType($resourceTypeString, $alias);
                }
            )
        );
    }

    private function getRawConfiguredResourceTypes(): array
    {
        $settings = $this->getSettings();
        if (isset($settings['paths']) && is_array($settings['paths'])) {
            return $settings['paths'];
        }

        return $settings['paths.'] ?? [];
    }

    /**
     * If no explicit path is configured use the current key
     *
     * @param array $configuration
     * @param string $path
     * @return array
     */
    private function preparePath(array $configuration, string $path): array
    {
        $resourceType = $configuration['path'] ?? trim($path, '.');
        $normalizeResourceType = Utility::normalizeResourceType($resourceType);
        $configuration['path'] = $normalizeResourceType;

        return [$configuration, $normalizeResourceType];
    }

    private function detectCacheLifetimeConfiguration(array $configuration): int
    {
        if (isset($configuration['cacheLifeTime']) && is_numeric($configuration['cacheLifeTime'])) {
            return (int)$configuration['cacheLifeTime'];
        }
        if (isset($configuration['cacheLifetime']) && is_numeric($configuration['cacheLifetime'])) {
            return (int)$configuration['cacheLifetime'];
        }

        return -1;
    }

    private function detectExpiresHeaderLifetimeConfiguration(array $configuration): int
    {
        if (isset($configuration['expiresHeaderLifetime']) && is_numeric($configuration['expiresHeaderLifetime'])) {
            return (int)$configuration['expiresHeaderLifetime'];
        }

        return -1;
    }
}
