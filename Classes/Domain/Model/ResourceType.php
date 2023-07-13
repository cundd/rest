<?php

declare(strict_types=1);

namespace Cundd\Rest\Domain\Model;

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
        $this->resourceType = (string)$resourceType;
    }

    public function __toString()
    {
        return $this->resourceType;
    }

    public static function assertValidResourceType(ResourceType|string $resourceType): void
    {
        if (str_contains((string)$resourceType, '/')) {
            throw new InvalidArgumentException('Resource Type must not contain a slash');
        }
    }
}
