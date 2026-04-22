<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\Request\ResourceType;

/**
 * Interface for configuration providers
 *
 * @phpstan-type RawConfiguration array{
 *     path?:string,
 *     read?:'deny'|'require'|'allow',
 *     write?:'deny'|'require'|'allow',
 *     handlerClass?:class-string,
 *     dataProviderClass?:class-string,
 *     cacheLifetime?:int,
 *     expiresHeaderLifetime?:int
 * }
 * @phpstan-type Settings array{paths?:array<string,RawConfiguration>}
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
     * Return the setting with the given key
     */
    public function getSetting(string $keyPath, mixed $defaultValue = null): mixed;

    /**
     * Return the settings read from the TypoScript
     *
     * @return Settings
     */
    public function getSettings(): array;

    /**
     * Return the paths configured in the settings
     *
     * @return ResourceConfiguration[]
     */
    public function getConfiguredResources(): array;

    /**
     * Return the configuration matching the given resource type
     */
    public function getResourceConfiguration(
        ResourceType $resourceType,
    ): ?ResourceConfiguration;
}
