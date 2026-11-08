<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Request\ResourceType;
use LogicException;

interface ClassLoadingInterface
{
    /**
     * Return the domain model repository for the models the given API Resource Type points to
     *
     * @param ResourceType $resourceType API Resource Type to get the repository for
     *
     * @throws LogicException if no repository could be found
     */
    public function getRepositoryForResourceType(ResourceType $resourceType): object;

    /**
     * Return the domain model repository class name for the given API Resource Type
     *
     * @param ResourceType $resourceType API Resource Type to get the repository for
     */
    public function getRepositoryClassForResourceType(ResourceType $resourceType): string;

    /**
     * Return the domain model class name for the given API Resource Type
     */
    public function getModelClassForResourceType(ResourceType $resourceType): string;
}
