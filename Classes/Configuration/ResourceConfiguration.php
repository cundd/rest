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
     * ResourceConfiguration constructor
     *
     * @param ResourceType $resourceType
     * @param Access       $read
     * @param Access       $write
     * @param int          $cacheLiveTime
     */
    public function __construct(ResourceType $resourceType, Access $read, Access $write, $cacheLiveTime)
    {
        $this->resourceType = $resourceType;
        $this->read = $read;
        $this->write = $write;
        $this->cacheLiveTime = (int)$cacheLiveTime;
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
}
