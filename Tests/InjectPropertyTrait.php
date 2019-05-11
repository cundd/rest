<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests;

use ReflectionProperty;

trait InjectPropertyTrait
{
    /**
     * @param mixed  $propertyValue
     * @param string $propertyKey
     * @param object $object
     * @return object
     */
    public static function injectPropertyIntoObject($propertyValue, $propertyKey, $object)
    {
        $reflectionMethod = new ReflectionProperty(get_class($object), $propertyKey);
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->setValue($object, $propertyValue);

        return $object;
    }
}
