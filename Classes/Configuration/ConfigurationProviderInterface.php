<?php
declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\Domain\Model\ResourceType;

/**
 * Interface for configuration providers
 */
interface ConfigurationProviderInterface
{
    /**
     * The request want's to write data
     */
    public const ACCESS_METHOD_WRITE = 'write';
    /**
     * The request want's to read data
     */
    public const ACCESS_METHOD_READ = 'read';

    /**
     * Returns the setting with the given key
     *
     * @param string $keyPath
     * @param mixed  $defaultValue
     * @return mixed
     */
    public function getSetting(string $keyPath, $defaultValue = null);

    /**
     * Returns the settings read from the TypoScript
     *
     * @return array
     */
    public function getSettings(): array;

    /**
     * Returns the paths configured in the settings
     *
     * @return ResourceConfiguration[]
     */
    public function getConfiguredResources(): array;

    /**
     * Returns the configuration matching the given resource type
     *
     * @param ResourceType $resourceType
     * @return ResourceConfiguration|null
     */
    public function getResourceConfiguration(ResourceType $resourceType): ?ResourceConfiguration;
}
