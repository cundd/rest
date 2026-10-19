<?php

declare(strict_types=1);

namespace Cundd\Rest\Request;

use Cundd\Rest\Exception\EmptyResourceTypeException;
use Cundd\Rest\Exception\InvalidResourceTypeException;

/**
 * The resource type is an identifier for a model class.
 * It is the first segment of the request path, after alias mapping has been applied.
 *
 * TODO: Check if the ResourceType should be normalized at construction time (@see \Cundd\Rest\DataProvider\Utility::normalizeResourceType($resourceType))
 */
class ResourceType
{
    private string $resourceType;

    public function __construct(ResourceType|string $resourceType)
    {
        $this->assertValidResourceType($resourceType);
        $this->resourceType = (string) $resourceType;
    }

    public function __toString(): string
    {
        return $this->resourceType;
    }

    public static function assertValidResourceType(
        ResourceType|string $resourceType,
    ): void {
        $resourceTypeString = trim((string) $resourceType);
        if ('' === $resourceTypeString) {
            throw new EmptyResourceTypeException(
                'Resource Type must not be empty',
                1792395322
            );
        }

        if (str_contains($resourceTypeString, '/')) {
            throw new InvalidResourceTypeException(
                'Resource Type must not contain a slash',
                1792395324
            );
        }

        $allowedSpecialCharacters = ['_', '-', '*'];
        if (!ctype_alnum(str_replace(
            $allowedSpecialCharacters,
            '',
            $resourceTypeString
        ))) {
            throw new InvalidResourceTypeException(
                sprintf(
                    'Resource Type must contain only `a-zA-Z0-9%s`. String `%s` given',
                    implode($allowedSpecialCharacters),
                    $resourceTypeString
                ),
                1792395329
            );
        }
    }
}
