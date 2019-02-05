<?php


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
     * ResourceConfiguration constructor
     *
     * @param ResourceType $resourceType
     * @param Access       $read
     * @param Access       $write
     * @param int          $cacheLifetime
     * @param string       $handlerClass
     * @param string[]     $aliases
     */
    public function __construct(
        ResourceType $resourceType,
        Access $read,
        Access $write,
        $cacheLifetime,
        $handlerClass,
        array $aliases
    ) {
        $this->resourceType = $resourceType;
        $this->read = $read;
        $this->write = $write;
        $this->cacheLifetime = (int)$cacheLifetime;
        $this->handlerClass = (string)$handlerClass;
        $this->assertStringArray($aliases);
        $this->aliases = $aliases;
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
     * @return int
     * @deprecated will be removed in 4.0. Use getCacheLifetime() instead
     */
    public function getCacheLiveTime()
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
