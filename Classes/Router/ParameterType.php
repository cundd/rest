<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02.01.17
 * Time: 18:18
 */

namespace Cundd\Rest\Router;


abstract class ParameterType
{
    /**
     * Extract the parameters from the given pattern
     *
     * @param string $pattern
     * @return string[]
     */
    public static function extractParameterTypesFromPattern($pattern)
    {
        return array_filter(array_map([__CLASS__, 'createParameter'], explode('/', (string)$pattern)));
    }

    /**
     * @param string $input
     * @return string
     */
    private static function createParameter($input)
    {
        $startsWithBracket = substr($input, 0, 1) === '{';
        $endsWithBracket = substr($input, -1) === '}';

        if (!$startsWithBracket && !$endsWithBracket) {
            return null;
        }

        if ($startsWithBracket && $endsWithBracket) {
            $type = substr($input, 1, -1);
            switch (strtolower($type)) {
                case 'integer':
                case 'int':
                    return ParameterTypeInterface::INTEGER;

                case 'slug':
                case 'string':
                    return ParameterTypeInterface::SLUG;

                case 'float':
                case 'double':
                case 'number':
                    return ParameterTypeInterface::FLOAT;

                case 'bool':
                case 'boolean':
                    return ParameterTypeInterface::BOOLEAN;

                default:
                    throw new \InvalidArgumentException(sprintf('Invalid parameter type "%s"', $type));
            }
        }

        throw new \InvalidArgumentException(sprintf('Unmatched brackets in path segment "%s"', $input));
    }
}
