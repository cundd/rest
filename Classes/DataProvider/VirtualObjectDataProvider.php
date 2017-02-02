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

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 26.03.14
 * Time: 20:34
 */

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\Persistence\RepositoryInterface;
use Cundd\Rest\VirtualObject\VirtualObject;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Property\Exception;

/**
 * Data Provider for Virtual Objects
 */
class VirtualObjectDataProvider extends DataProvider
{
    /**
     * @var array<\Cundd\Rest\VirtualObject\ObjectConverter>
     */
    protected $objectConverterMap = array();

    /**
     * @var \Cundd\Rest\VirtualObject\ConfigurationFactory
     * @inject
     */
    protected $configurationFactory;

    /**
     * Returns the Object Converter with the currently matching configuration
     *
     * @param ResourceType|string $resourceType
     * @return \Cundd\Rest\VirtualObject\ObjectConverter
     */
    public function getObjectConverterForResourceType(ResourceType $resourceType)
    {
        $resourceTypeString = (string)$resourceType;
        if (!isset($this->objectConverterMap[$resourceTypeString])) {
            /** @var ObjectConverter $objectConverter */
            $objectConverter = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\ObjectConverter');
            $objectConverter->setConfiguration($this->getConfigurationForResourceType($resourceType));

            $this->objectConverterMap[$resourceTypeString] = $objectConverter;

            return $objectConverter;
        }

        return $this->objectConverterMap[$resourceTypeString];
    }

    /**
     * Returns the Configuration for the given resource type
     *
     * @param ResourceType $resourceType
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException
     * @return ConfigurationInterface
     */
    public function getConfigurationForResourceType(ResourceType $resourceType)
    {
        $virtualResourceTypeString = substr(
            $resourceType,
            strpos($resourceType, '-') + 1
        ); // Strip the "VirtualObject-" from the resource type
        if (!$virtualResourceTypeString) {
            throw new MissingConfigurationException('Could not get configuration for empty resource type', 1395932408);
        }

        try {
            return $this->configurationFactory->createFromTypoScriptForResourceType(
                new ResourceType($virtualResourceTypeString)
            );
        } catch (MissingConfigurationException $exception) {
            return null;
        }
    }

    /**
     * Returns the domain model repository class name for the given resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getRepositoryClassForResourceType(ResourceType $resourceType)
    {
        return 'Cundd\\Rest\\VirtualObject\\Persistence\\Repository';
    }

    /**
     * Returns the domain model repository for the models the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
     */
    public function getRepositoryForResourceType(ResourceType $resourceType)
    {
        $repositoryClass = $this->getRepositoryClassForResourceType($resourceType);
        /** @var \Cundd\Rest\VirtualObject\Persistence\RepositoryInterface|\TYPO3\CMS\Extbase\Persistence\RepositoryInterface $repository */
        $repository = $this->objectManager->get($repositoryClass);
        $repository->setConfiguration($this->getConfigurationForResourceType($resourceType));

        return $repository;
    }

    /**
     * Returns a domain model for the given API resource type and data
     * This method will load existing models.
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return DomainObjectInterface|VirtualObject
     */
    public function getModelWithDataForResourceType($data, ResourceType $resourceType)
    {
        // If no data is given return a new instance
        if (!$data) {
            return $this->getEmptyModelForResourceType($resourceType);
        } elseif (is_scalar($data)) { // If it is a scalar treat it as identity
            return $this->getModelWithIdentityForResourceType($data, $resourceType);
        }

        $data = $this->prepareModelData($data);
        try {
            $objectConverter = $this->getObjectConverterForResourceType($resourceType);
            $modelData = $objectConverter->convertFromVirtualObject($data);
            $model = $objectConverter->convertToVirtualObject($modelData);
        } catch (Exception $exception) {
            $model = null;

            $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }

        return $model;
    }

    /**
     * Returns a new domain model for the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the model for
     * @return DomainObjectInterface|VirtualObject
     */
    public function getEmptyModelForResourceType(ResourceType $resourceType)
    {
        return new VirtualObject();
    }

    /**
     * Returns the data from the given model
     *
     * @param DomainObjectInterface $model
     * @return array<mixed>
     */
    public function getModelData($model)
    {
        $properties = parent::getModelData($model);
        if ($properties === $model) {
            return array();
        }

        return $properties;
    }

    /**
     * Returns the property data from the given model
     *
     * @param DomainObjectInterface $model
     * @param string                $propertyKey
     * @return mixed
     */
    public function getModelProperty($model, $propertyKey)
    {
        /** @var VirtualObject $model */
        $modelData = $model->getData();
        if (isset($modelData[$propertyKey])) {
            return $modelData[$propertyKey];
        }

        return null;
    }

    /**
     * Adds or updates the given model in the repository for the given API resource type
     *
     * @param DomainObjectInterface $model
     * @param ResourceType          $resourceType The API resource type
     * @return void
     */
    public function saveModelForResourceType($model, ResourceType $resourceType)
    {
        /** @var VirtualObject $model */
        /** @var RepositoryInterface $repository */
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($repository) {
            $repository->registerObject($model);
            $this->persistAllChanges();
        }
    }

    /**
     * Persist all changes to the database
     */
    public function persistAllChanges()
    {
        // We don't have to do anything because changes are persisted live
    }
}
