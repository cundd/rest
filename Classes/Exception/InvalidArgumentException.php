<?php

namespace Cundd\Rest\Exception;


class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param mixed  $actualValue
     * @param string $expectType
     * @param string $argumentName
     * @return static
     */
    public static function buildException($actualValue, $expectType, $argumentName)
    {
        return new static(
            sprintf(
                'Expected argument "%s" to be of type %s, %s given',
                $argumentName,
                $expectType,
                is_object($actualValue) ? get_class($actualValue) : gettype($actualValue)
            )
        );
    }
}
