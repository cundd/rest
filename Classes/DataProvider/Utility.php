<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Request\ResourceType;
use InvalidArgumentException;

/**
 * A utility class with static methods for Data Providers
 */
final class Utility
{
    /**
     * Separator between vendor, extension and model in the API resource type
     */
    private const API_RESOURCE_TYPE_PART_SEPARATOR = '-';

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
     * @return array{0:string,1:string,2:string}
     */
    public static function getClassNamePartsForResourceType(
        ResourceType $resourceType,
    ): array {
        $resourceTypeString = (string) $resourceType;
        if ('' === $resourceTypeString) {
            return ['', '', ''];
        }
        if (str_contains($resourceTypeString, '_')) {
            $resourceTypeString = static::underscoredToUpperCamelCase($resourceTypeString);
        }
        $parts = explode(static::API_RESOURCE_TYPE_PART_SEPARATOR, $resourceTypeString, 3);
        if (count($parts) < 3) {
            array_unshift($parts, '');
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
     * @return class-string<object>
     */
    public static function getModelEntityForResourceType(
        ResourceType $resourceType,
    ): ?string {
        [$vendor, $extension, $model] = Utility::getClassNamePartsForResourceType(
            $resourceType,
        );
        $namespaceVersion = ($vendor ? $vendor . '\\' : '')
            . $extension
            . '\\Domain\\Model\\'
            . $model;
        $underscoreVersion = 'Tx_' . $extension . '_Domain_Model_' . $model;

        if (class_exists($namespaceVersion)) {
            return $namespaceVersion;
        } elseif (class_exists($underscoreVersion)) {
            return $underscoreVersion;
        } elseif (interface_exists($namespaceVersion)) {
            return $namespaceVersion;
        } elseif (interface_exists($underscoreVersion)) {
            return $underscoreVersion;
        }

        return null;
    }

    /**
     * Tries to generate the API resource type for the given class name
     *
     * @return ResourceType|bool Returns the resource type or FALSE if it couldn't be determined
     */
    public static function getResourceTypeForClassName(string $className): ResourceType|bool
    {
        if (!str_contains($className, '\\')) {
            if (str_starts_with($className, 'Tx_')) {
                $className = substr($className, 3);
            }
            $className = str_replace('_', '\\', $className);
        }

        $className = str_replace('\\Domain\\Model\\', '\\', $className);
        $classNameParts = array_map(
            self::camelCaseToLowerCaseUnderscored(...),
            explode('\\', $className)
        );

        try {
            return new ResourceType(implode(static::API_RESOURCE_TYPE_PART_SEPARATOR, $classNameParts));
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Transforms UpperCamelCase Resource Types into lower_case_underscore
     */
    public static function normalizeResourceType(ResourceType|string $resourceType): string
    {
        $resourceTypeString = trim((string) $resourceType, '.');

        return implode(
            '-',
            array_map(
                function (string $part): string {
                    if ('*' === $part) {
                        return '*';
                    }

                    return static::camelCaseToLowerCaseUnderscored($part);
                },
                explode('-', $resourceTypeString)
            )
        );
    }

    private static function underscoredToUpperCamelCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Convert a camelCase string to lowercase_underscore
     */
    private static function camelCaseToLowerCaseUnderscored(string $input): string
    {
        $value = preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $input);

        return mb_strtolower($value, 'utf-8');
    }
}
