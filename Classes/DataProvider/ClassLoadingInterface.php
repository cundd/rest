<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\VirtualObject\Persistence\Repository as RestRepository;
use LogicException;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface as TYPO3RepositoryInterface;

interface ClassLoadingInterface
{
    /**
     * Return the domain model repository for the models the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return object
     * @throws LogicException if no repository could be found
     */
    public function getRepositoryForResourceType(ResourceType $resourceType): object;

    /**
     * Return the domain model repository class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getRepositoryClassForResourceType(ResourceType $resourceType): string;

    /**
     * Return the domain model class name for the given API resource type
     *
     * @param ResourceType $resourceType
     * @return string
     */
    public function getModelClassForResourceType(ResourceType $resourceType): string;
}
