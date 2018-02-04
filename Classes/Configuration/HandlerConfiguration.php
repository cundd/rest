<?php


namespace Cundd\Rest\Configuration;


use Cundd\Rest\Domain\Model\ResourceType;

class HandlerConfiguration
{
    /**
     * @var ResourceType
     */
    private $resourceType;

    /**
     * @var string
     */
    private $className;

    /**
     * HandlerConfiguration constructor
     *
     * @param ResourceType $resourceType
     * @param string       $className
     */
    public function __construct(ResourceType $resourceType, $className)
    {
        $this->resourceType = $resourceType;
        $this->className = (string)$className;
    }

    /**
     * @return ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
