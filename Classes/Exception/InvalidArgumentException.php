<?php

declare(strict_types=1);

namespace Cundd\Rest\Exception;

final class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @return static
     */
    public static function buildException(
        mixed $actualValue,
        string $expectType,
        string $argumentName,
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
     * @phpstan-assert array<string,mixed> $variable
     */
    public static function assertAssociativeArray(
        mixed $variable,
        string $variableName,
    ): void {
        if (!is_array($variable)) {
            throw new static(sprintf(
                'Expected %s to be of type array, %s given',
                $variableName,
                get_debug_type($variable)
            ));
        }
        foreach (array_keys($variable) as $key) {
            if (!is_string($key)) {
                throw new static(sprintf(
                    'Expected keys of array %s to be of type string, %s given',
                    $variableName,
                    get_debug_type($key)
                ));
            }
        }
    }

    /**
     * @phpstan-assert-if-true array<mixed,string> $variable
     */
    public static function isStringArray(mixed $variable): bool
    {
        if (!is_array($variable)) {
            return false;
        }
        foreach ($variable as $item) {
            if (!is_string($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @phpstan-assert array<mixed,string> $variable
     */
    public static function assertStringArray(
        mixed $variable,
        string $variableName,
    ): void {
        if (!is_array($variable)) {
            throw new InvalidArgumentException(sprintf(
                'Expected %s to be of type array, %s given',
                $variableName,
                get_debug_type($variable)
            ));
        }
        if (!static::isStringArray($variable)) {
            throw new InvalidArgumentException(sprintf(
                'Expected items of array %s to be of type string',
                $variableName,
            ));
        }
    }

    /**
     * Assert that the input is either an object or NULL
     *
     * @phpstan-assert ?object $value
     */
    public static function assertObjectOrNull(
        mixed $value,
        ?string $argumentName = null,
    ): void {
        if (false === (is_null($value) || is_object($value))) {
            throw new static(
                sprintf(
                    '%s must be either NULL or an object, %s given',
                    $argumentName ?? 'Variable',
                    gettype($value)
                )
            );
        }
    }

    /**
     * Assert that the input is an object
     */
    public static function assertObject(
        mixed $value,
        ?string $argumentName = null,
    ): void {
        if (!is_object($value)) {
            throw new static(
                sprintf('%s must be an object %s given', $argumentName ?? 'Variable', gettype($value))
            );
        }
    }
}
