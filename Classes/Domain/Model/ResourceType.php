<?php

namespace Cundd\Rest\Domain\Model;

/**
 * The resource type is a identifier for a model class.
 * It is the first segment of the request path, after alias mapping has been applied.
 */
class ResourceType
{
    /**
     * @var string
     */
    private $resourceType;

    /**
     * Resource Type constructor
     *
     * @param $resourceType
     */
    public function __construct($resourceType)
    {
        $this->assertValidResourceType($resourceType);
        $this->resourceType = (string)$resourceType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->resourceType;
    }

    /**
     * @param string $resourceType
     */
    public static function assertValidResourceType($resourceType)
    {
        if (!$resourceType instanceof self && !is_string($resourceType)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Resource Type must be of type string "%s" given',
                    is_object($resourceType) ? get_class($resourceType) : gettype($resourceType)
                )
            );
        }

        if (false !== strpos((string)$resourceType, '/')) {
            throw new \InvalidArgumentException('Resource Type must not contain a slash');
        }
    }
}
