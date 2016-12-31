<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface DataProviderInterface extends SingletonInterface
{
    /**
     * Returns the domain model repository for the models the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
     */
    public function getRepositoryForResourceType(ResourceType $resourceType);

    /**
     * Returns the domain model repository class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getRepositoryClassForResourceType(ResourceType $resourceType);

    /**
     * Returns all domain model for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return DomainObjectInterface[]|QueryResultInterface
     */
    public function getAllModelsForResourceType(ResourceType $resourceType);

    /**
     * Returns a domain model for the given API resource type and data
     * This method will load existing models.
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return DomainObjectInterface
     */
    public function getModelWithDataForResourceType($data, ResourceType $resourceType);

    /**
     * Returns a domain model for the given API resource type and data
     * Even if the data contains an identifier, the existing model will not be loaded.
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return DomainObjectInterface
     */
    public function getNewModelWithDataForResourceType($data, ResourceType $resourceType);

    /**
     * Returns the domain model class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getModelClassForResourceType(ResourceType $resourceType);

    /**
     * Returns the data from the given model
     *
     * @param DomainObjectInterface|object $model
     */
    public function getModelData($model);

    /**
     * Returns the property data from the given model
     *
     * @param DomainObjectInterface $model
     * @param string                $propertyKey
     * @return mixed
     */
    public function getModelProperty($model, $propertyKey);

    /**
     * Adds or updates the given model in the repository for the
     * given API resource type
     *
     * @param DomainObjectInterface $model
     * @param ResourceType          $resourceType The API resource type
     * @return void
     */
    public function saveModelForResourceType($model, ResourceType $resourceType);

    /**
     * Tells the Data Provider to replace the given old model with the new one
     * in the repository for the given API resource type
     *
     * @param DomainObjectInterface $oldModel
     * @param DomainObjectInterface $newModel
     * @param ResourceType          $resourceType The API resource type
     * @return void
     */
    public function replaceModelForResourceType($oldModel, $newModel, ResourceType $resourceType);

    /**
     * Adds or updates the given model in the repository for the
     * given API resource type
     *
     * @param DomainObjectInterface $model
     * @param ResourceType          $resourceType The API resource type
     * @return void
     */
    public function removeModelForResourceType($model, ResourceType $resourceType);

    /**
     * Persist all changes to the database
     */
    public function persistAllChanges();
}
