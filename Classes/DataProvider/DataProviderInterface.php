<?php

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface DataProviderInterface extends SingletonInterface
{
    /**
     * Return the domain model repository for the models the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
     */
    public function getRepositoryForResourceType(ResourceType $resourceType);

    /**
     * Return the domain model repository class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getRepositoryClassForResourceType(ResourceType $resourceType);

    /**
     * Return all domain model for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return object[]|DomainObjectInterface[]|QueryResultInterface
     */
    public function getAllModelsForResourceType(ResourceType $resourceType);

    /**
     * Return a domain model for the given API resource type and data
     *
     * This method will load existing models
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return object|DomainObjectInterface
     */
    public function getModelWithDataForResourceType($data, ResourceType $resourceType);

    /**
     * Return a domain model for the given API resource type and data
     *
     * Even if the data contains an identifier, the existing model will not be loaded
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return object|DomainObjectInterface
     */
    public function getNewModelWithDataForResourceType($data, ResourceType $resourceType);

    /**
     * Return the domain model class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getModelClassForResourceType(ResourceType $resourceType);

    /**
     * Return the data from the given model
     *
     * @param object|DomainObjectInterface $model
     * @return array
     */
    public function getModelData($model);

    /**
     * Return the property data from the given model
     *
     * @param object|DomainObjectInterface $model
     * @param string                       $propertyKey
     * @return mixed
     */
    public function getModelProperty($model, $propertyKey);

    /**
     * Add or update the given model in the repository for the given API resource type
     *
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
     * @return void
     */
    public function saveModelForResourceType($model, ResourceType $resourceType);

    /**
     * Tell the Data Provider to replace the given old model with the new one in the repository for the given API resource type
     *
     * @param object|DomainObjectInterface $oldModel
     * @param object|DomainObjectInterface $newModel
     * @param ResourceType                 $resourceType The API resource type
     * @return void
     */
    public function replaceModelForResourceType($oldModel, $newModel, ResourceType $resourceType);

    /**
     * Add or updates the given model in the repository for the given API resource type
     *
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
     * @return void
     */
    public function removeModelForResourceType($model, ResourceType $resourceType);

    /**
     * Persist all changes to the database
     */
    public function persistAllChanges();
}
