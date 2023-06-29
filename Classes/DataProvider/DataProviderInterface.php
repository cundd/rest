<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Exception;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface DataProviderInterface
{
    /**
     * Return all Domain Models for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return object[]|DomainObjectInterface[]|QueryResultInterface
     */
    public function fetchAllModels(ResourceType $resourceType): iterable;

    /**
     * Return the number of all Domain Models for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return int
     */
    public function countAllModels(ResourceType $resourceType): int;

    /**
     * Return a Domain Model for the given API resource type and data
     *
     * This method will load existing models
     *
     * @param array|string|int $identifier   Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return object|DomainObjectInterface|null Returns the Domain Model or NULL if it was not found
     */
    public function fetchModel($identifier, ResourceType $resourceType): ?object;

    /**
     * Create a new Domain Model with the given data
     *
     * Implementations are free to decide if identifiers are accepted (e.g. an exception will be thrown for Extbase
     * Models if the property `uid` or `__identity` is given. `Virtual Objects` on the other hand accept identifier
     * properties)
     *
     * @param array        $data         Data of the new model
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return object|DomainObjectInterface|Exception Return the created Model on success otherwise an Exception
     */
    public function createModel(array $data, ResourceType $resourceType);

    /**
     * Converts the data into an instance of the Domain Model for the Resource Type
     *
     * @param array        $data
     * @param ResourceType $resourceType
     * @return object|DomainObjectInterface|null
     */
    public function convertIntoModel(array $data, ResourceType $resourceType): ?object;

    /**
     * Extract the data from the given Model or one of it's properties
     *
     * @param mixed $model
     * @return array|null|int|bool|string|float
     */
    public function getModelData($model);

    /**
     * Return the property data from the given Model
     *
     * @param object|DomainObjectInterface $model
     * @param string                       $propertyParameter
     * @return mixed
     */
    public function getModelProperty(
        object $model,
        string $propertyParameter
    );

    /**
     * Add or update the given Model in the repository
     *
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
     * @return void
     */
    public function saveModel(
        object $model,
        ResourceType $resourceType
    ): void;

    /**
     * Remove the given model from the repository for the given API resource type
     *
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
     * @return void
     */
    public function removeModel(
        object $model,
        ResourceType $resourceType
    ): void;
}
