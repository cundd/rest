<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use InvalidArgumentException;

/**
 * A utility class with static methods for Data Providers
 */
class Utility
{
    /**
     * Separator between vendor, extension and model in the API resource type
     */
    private const API_RESOURCE_TYPE_PART_SEPARATOR = '-';

    /**
     * Mapping from singular to plural
     *
     * @var array<string, string>
     */
    protected static array $singularToPlural = [];

    /**
     * Returns an array of class name parts including vendor, extension and domain model
     *
     * Example:
     *   array(
     *     Vendor
     *     MyExt
     *     MyModel
     *   )
     *
     * @param ResourceType $resourceType
     * @param bool         $convertPlural Indicates if plural resource names should be converted
     * @return array
     */
    public static function getClassNamePartsForResourceType(
        ResourceType $resourceType,
        bool $convertPlural = true
    ): array {
        $resourceTypeString = (string)$resourceType;
        if ('' === $resourceTypeString) {
            return ['', '', ''];
        }
        if (strpos($resourceTypeString, '_') !== false) {
            $resourceTypeString = static::underscoredToUpperCamelCase($resourceTypeString);
        }
        $parts = explode(static::API_RESOURCE_TYPE_PART_SEPARATOR, $resourceTypeString, 3);
        if (count($parts) < 3) {
            array_unshift($parts, '');
        }

        if ($convertPlural && $parts) {
            $lastPartIndex = count($parts) - 1;
            $parts[$lastPartIndex] = static::singularize($parts[$lastPartIndex]);
        }

        return [
            ucfirst($parts[0]),
            ucfirst($parts[1]),
            isset($parts[2])
                ? str_replace(' ', '\\', ucwords(str_replace('-', ' ', $parts[2])))
                : '',
        ];
    }

    /**
     * Return the Domain Model class or interface name for the given API resource type
     *
     * @param ResourceType $resourceType
     * @param bool         $convertPlural
     * @return string|null
     */
    public static function getModelEntityForResourceType(
        ResourceType $resourceType,
        bool $convertPlural = true
    ): ?string {
        [$vendor, $extension, $model] = Utility::getClassNamePartsForResourceType($resourceType, $convertPlural);
        $namespaceVersion = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Model\\' . $model;
        $underscoreVersion = 'Tx_' . $extension . '_Domain_Model_' . $model;

        if (class_exists($namespaceVersion)) {
            return $namespaceVersion;
        } elseif (class_exists($underscoreVersion)) {
            return $underscoreVersion;
        } elseif (interface_exists($namespaceVersion)) {
            return $namespaceVersion;
        } elseif (interface_exists($underscoreVersion)) {
            return $underscoreVersion;
        } else {
            return null;
        }
    }

    /**
     * Tries to generate the API resource type for the given class name
     *
     * @param string $className
     * @return ResourceType|bool Returns the resource type or FALSE if it couldn't be determined
     */
    public static function getResourceTypeForClassName(string $className)
    {
        if (strpos($className, '\\') === false) {
            if (substr($className, 0, 3) === 'Tx_') {
                $className = substr($className, 3);
            }
            $className = str_replace('_', '\\', $className);
        }

        $className = str_replace('\\Domain\\Model\\', '\\', $className);
        $classNameParts = array_map(
            [get_called_class(), 'camelCaseToLowerCaseUnderscored'],
            explode('\\', $className)
        );

        try {
            return new ResourceType(implode(static::API_RESOURCE_TYPE_PART_SEPARATOR, $classNameParts));
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }

    /**
     * Tries to convert an english plural into it's singular.
     *
     * @param string $word
     * @return string
     */
    public static function singularize(string $word): string
    {
        $customMapping = array_search($word, static::$singularToPlural, true);
        if ($customMapping !== false) {
            return $customMapping;
        }
        $customMapping = array_search(strtolower($word), static::$singularToPlural, true);
        if ($customMapping !== false) {
            return $customMapping;
        }

        // Here is the list of rules. To add a scenario,
        // Add the plural ending as the key and the singular
        // ending as the value for that key. This could be
        // turned into a preg_replace and probably will be
        // eventually, but for now, this is what it is.
        //
        // Note: The first rule has a value of false since
        // we don't want to mess with words that end with
        // double 's'. We normally wouldn't have to create
        // rules for words we don't want to mess with, but
        // the last rule (s) would catch double (ss) words
        // if we didn't stop before it got to that rule.
        $rules = [
            'ss'  => false,
            'os'  => 'o',
            'ies' => 'y',
            'xes' => 'x',
            'oes' => 'o',
            'ves' => 'f',
            's'   => '',
        ];
        // Loop through all the rules and do the replacement.
        foreach (array_keys($rules) as $key) {
            // If the end of the word doesn't match the key,
            // it's not a candidate for replacement. Move on
            // to the next plural ending.
            if (substr($word, (strlen($key) * -1)) != $key) {
                continue;
            }
            // If the value of the key is false, stop looping
            // and return the original version of the word.
            if ($key === false) {
                return $word;
            }
            // We've made it this far, so we can do the
            // replacement.
            return substr($word, 0, strlen($word) - strlen($key)) . $rules[$key];
        }

        return $word;
    }

    /**
     * Add a mapping from singular to plural
     *
     * @param string $singular
     * @param string $plural
     */
    public static function registerSingularForPlural(string $singular, string $plural): void
    {
        static::$singularToPlural[$singular] = $plural;
    }

    /**
     * Transforms UpperCamelCase Resource Types into lower_case_underscore
     *
     * @param string|ResourceType $resourceType
     * @return string
     */
    public static function normalizeResourceType(ResourceType|string $resourceType): string
    {
        $resourceTypeString = trim((string)$resourceType, '.');

        return implode(
            '-',
            array_map(
                function (string $part): string {
                    if ($part === '*') {
                        return '*';
                    }

                    return static::camelCaseToLowerCaseUnderscored($part);
                },
                explode('-', $resourceTypeString)
            )
        );
    }

    /**
     * @param string $string
     * @return string
     */
    private static function underscoredToUpperCamelCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Convert a camelCase string to lowercase_underscore
     *
     * @param string $input
     * @return string
     */
    private static function camelCaseToLowerCaseUnderscored(string $input): string
    {
        $value = preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $input);

        return is_callable('mb_strtolower') ? mb_strtolower($value, 'utf-8') : strtolower($value);
    }
}
