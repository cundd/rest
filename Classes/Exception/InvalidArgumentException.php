<?php

declare(strict_types=1);

namespace Cundd\Rest\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param mixed  $actualValue
     * @param string $expectType
     * @param string $argumentName
     * @return static
     */
    public static function buildException(
        $actualValue,
        string $expectType,
        string $argumentName
    ): InvalidArgumentException {
        return new static(
            sprintf(
                'Expected argument "%s" to be of type %s, %s given',
                $argumentName,
                $expectType,
                is_object($actualValue) ? get_class($actualValue) : gettype($actualValue)
            )
        );
    }

    /**
     * Assert that the input is either an object or NULL
     *
     * @param mixed       $value
     * @param string|null $argumentName
     */
    public static function assertObjectOrNull($value, ?string $argumentName = null): void
    {
        if (false === (is_null($value) || is_object($value))) {
            throw new static(
                sprintf('%s must be either NULL or an object, %s given', $argumentName ?? 'Variable', gettype($value))
            );
        }
    }

    /**
     * Assert that the input is an object
     *
     * @param mixed       $value
     * @param string|null $argumentName
     */
    public static function assertObject($value, ?string $argumentName = null): void
    {
        if (!is_object($value)) {
            throw new static(
                sprintf('%s must be an object %s given', $argumentName ?? 'Variable', gettype($value))
            );
        }
    }
}
