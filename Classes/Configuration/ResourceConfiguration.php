<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Exception\InvalidArgumentException;

class ResourceConfiguration
{
    /**
     * @var ResourceType
     */
    private $resourceType;

    /**
     * @var Access
     */
    private $read;

    /**
     * @var Access
     */
    private $write;

    /**
     * @var int
     */
    private $cacheLifetime;

    /**
     * @var string
     */
    private $handlerClass;

    /**
     * @var string[]
     */
    private $aliases;

    /**
     * @var string
     */
    private $dataProviderClass;

    /**
     * @var int
     */
    private $expiresHeaderLifetime;

    /**
     * ResourceConfiguration constructor
     *
     * @param string[] $aliases
     */
    public function __construct(
        ResourceType $resourceType,
        Access $read,
        Access $write,
        int $cacheLifetime,
        string $handlerClass,
        string $dataProviderClass,
        array $aliases,
        int $expiresHeaderLifetime = -1,
    ) {
        $this->resourceType = $resourceType;
        $this->read = $read;
        $this->write = $write;
        $this->cacheLifetime = $cacheLifetime;
        $this->handlerClass = $handlerClass;
        $this->assertStringArray($aliases);
        $this->aliases = $aliases;
        $this->dataProviderClass = $dataProviderClass;
        $this->expiresHeaderLifetime = $expiresHeaderLifetime;
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function getRead(): Access
    {
        return $this->read;
    }

    public function getWrite(): Access
    {
        return $this->write;
    }

    public function getCacheLifetime(): int
    {
        return $this->cacheLifetime;
    }

    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

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

    private function assertStringArray(array $aliases)
    {
        foreach ($aliases as $alias) {
            if (!is_string($alias)) {
                throw new InvalidArgumentException('Only strings are allowed as aliases');
            }
        }
    }
}
