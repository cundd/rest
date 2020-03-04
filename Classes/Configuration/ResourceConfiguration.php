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
    private $cacheLifetime = 0;

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
     * @param ResourceType $resourceType
     * @param Access       $read
     * @param Access       $write
     * @param int          $cacheLifetime
     * @param string       $handlerClass
     * @param string       $dataProviderClass
     * @param string[]     $aliases
     * @param int          $expiresHeaderLifetime
     */
    public function __construct(
        ResourceType $resourceType,
        Access $read,
        Access $write,
        int $cacheLifetime,
        string $handlerClass,
        string $dataProviderClass,
        array $aliases,
        int $expiresHeaderLifetime = -1
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

    /**
     * @return ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @return Access
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * @return Access
     */
    public function getWrite()
    {
        return $this->write;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return $this->cacheLifetime;
    }

    /**
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->handlerClass;
    }

    /**
     * @return string
     */
    public function getDataProviderClass(): string
    {
        return $this->dataProviderClass;
    }

    /**
     * @return int
     */
    public function getExpiresHeaderLifetime(): int
    {
        return $this->expiresHeaderLifetime;
    }

    /**
     * @return string[]
     */
    public function getAliases()
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
