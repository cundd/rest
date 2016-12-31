<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 29.12.16
 * Time: 21:11
 */

namespace Cundd\Rest\Domain\Model;


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
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
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
