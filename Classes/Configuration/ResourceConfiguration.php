<?php


namespace Cundd\Rest\Configuration;


use Cundd\Rest\Domain\Model\ResourceType;

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
    private $cacheLiveTime = 0;

    /**
     * @var string
     */
    private $handlerClass;

    /**
     * ResourceConfiguration constructor
     *
     * @param ResourceType $resourceType
     * @param Access       $read
     * @param Access       $write
     * @param int          $cacheLiveTime
     * @param string       $handlerClass
     */
    public function __construct(ResourceType $resourceType, Access $read, Access $write, $cacheLiveTime, $handlerClass)
    {
        $this->resourceType = $resourceType;
        $this->read = $read;
        $this->write = $write;
        $this->cacheLiveTime = (int)$cacheLiveTime;
        $this->handlerClass = (string)$handlerClass;
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
    public function getCacheLiveTime()
    {
        return $this->cacheLiveTime;
    }

    /**
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->handlerClass;
    }
}
