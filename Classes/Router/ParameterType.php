<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use InvalidArgumentException;

abstract class ParameterType
{
    /**
     * Extract the parameters from the given pattern
     *
     * @param string $pattern
     * @return string[]
     */
    public static function extractParameterTypesFromPattern(string $pattern): array
    {
        return array_filter(array_map([__CLASS__, 'createParameter'], self::splitPattern($pattern)));
    }

    /**
     * @param string $input
     * @return string|null
     */
    private static function createParameter(string $input): ?string
    {
        $startsWithBracket = str_starts_with($input, '{');
        $endsWithBracket = str_ends_with($input, '}');

        if (!$startsWithBracket && !$endsWithBracket) {
            return null;
        }

        $bracketsMatch = $startsWithBracket && $endsWithBracket;
        if (!$bracketsMatch) {
            throw new InvalidArgumentException(sprintf('Unmatched brackets in path segment "%s"', $input));
        }

        $type = substr($input, 1, -1);

        return match (strtolower($type)) {
            'integer', 'int' => ParameterTypeInterface::INTEGER,
            'slug', 'string' => ParameterTypeInterface::SLUG,
            'raw' => ParameterTypeInterface::RAW,
            'float', 'double', 'number' => ParameterTypeInterface::FLOAT,
            'bool', 'boolean' => ParameterTypeInterface::BOOLEAN,
            default => throw new InvalidArgumentException(sprintf('Invalid parameter type "%s"', $type)),
        };
    }

    private static function splitPattern(string $pattern): array
    {
        return array_reduce(
            explode('/', $pattern),
            function (array $carry, string $item): array {
                return array_merge($carry, explode('.', $item));
            },
            []
        );
    }
}
