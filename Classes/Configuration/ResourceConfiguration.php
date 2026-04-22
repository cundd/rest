<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Request\ResourceType;

class ResourceConfiguration
{
    /**
     * @param string[]                                   $aliases
     * @param class-string<HandlerInterface>|string      $handlerClass
     * @param class-string<DataProviderInterface>|string $dataProviderClass
     */
    public function __construct(
        public readonly ResourceType $resourceType,
        public readonly Access $readAccess,
        public readonly Access $writeAccess,
        public readonly int $cacheLifetime,
        public readonly string $handlerClass,
        public readonly string $dataProviderClass,
        public readonly array $aliases,
        public readonly int $expiresHeaderLifetime = -1,
    ) {
        $this->assertStringArray($aliases);
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function getRead(): Access
    {
        return $this->readAccess;
    }

    public function getWrite(): Access
    {
        return $this->writeAccess;
    }

    public function getCacheLifetime(): int
    {
        return $this->cacheLifetime;
    }

    /**
     * @return class-string<HandlerInterface>|string
     */
    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    /**
     * @return class-string<DataProviderInterface>|string
     */
    public function getDataProviderClass(): string
    {
        return $this->dataProviderClass;
    }

    public function getExpiresHeaderLifetime(): int
    {
        return $this->expiresHeaderLifetime;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @phpstan-assert array<string> $aliases
     *
     * @param array<int,mixed> $aliases
     */
    private function assertStringArray(array $aliases): void
    {
        foreach ($aliases as $alias) {
            if (!is_string($alias)) {
                throw new InvalidArgumentException('Only strings are allowed as aliases');
            }
        }
    }
}
