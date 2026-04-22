<?php

declare(strict_types=1);

namespace Cundd\Rest\Request;

use InvalidArgumentException;

/**
 * The resource type is an identifier for a model class.
 * It is the first segment of the request path, after alias mapping has been applied.
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
            // TODO: Check if this should be prevented
            return;
        }

        if (str_contains($resourceTypeString, '/')) {
            throw new InvalidArgumentException(
                'Resource Type must not contain a slash'
            );
        }

        $allowedSpecialCharacters = ['_', '-', '*'];
        if (!ctype_alnum(str_replace(
            $allowedSpecialCharacters,
            '',
            $resourceTypeString
        ))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Resource Type must contain only `a-zA-Z0-9%s`. String `%s` given',
                    implode($allowedSpecialCharacters),
                    $resourceTypeString
                )
            );
        }
    }
}
