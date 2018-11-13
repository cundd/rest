<?php

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface DataProviderInterface extends SingletonInterface
{
    /**
     * Return all Domain Models for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return object[]|DomainObjectInterface[]|QueryResultInterface
     */
    public function fetchAllModels(ResourceType $resourceType);

    /**
     * Return a Domain Model for the given API resource type and data
     *
     * This method will load existing models
     *
     * @param array|string|int $identifier   Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return object|DomainObjectInterface|null Returns the Domain Model or NULL if it was not found
     */
    public function fetchModel($identifier, ResourceType $resourceType);

    /**
     * Create a new Domain Model with the given data
     *
     * Even if the data contains an identifier, the existing model will **not** be loaded
     *
     * @param array        $data         Data of the new model
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return object|DomainObjectInterface|\Exception Return the created Model on success otherwise an Exception
     */
    public function createModel(array $data, ResourceType $resourceType);

    /**
     * Converts the data into an instance of the Domain Model for the Resource Type
     *
     * @param array        $data
     * @param ResourceType $resourceType
     * @return object|DomainObjectInterface
     */
    public function convertIntoModel(array $data, ResourceType $resourceType);

    /**
     * Return the data from the given Model
     *
     * @param object|DomainObjectInterface $model
     * @return array
     */
    public function getModelData($model);

    /**
     * Return the property data from the given Model
     *
     * @param object|DomainObjectInterface $model
     * @param string                       $propertyKey
     * @return mixed
     */
    public function getModelProperty($model, $propertyKey);

    /**
     * Add or update the given Model in the repository
     *
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
     * @return void
     */
    public function saveModel($model, ResourceType $resourceType);

    /**
     * Add or updates the given model in the repository for the given API resource type
     *
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
     * @return void
     */
    public function removeModel($model, ResourceType $resourceType);
}
