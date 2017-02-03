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
use Cundd\Rest\ObjectManager;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * DataProvider instance
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var \Cundd\Rest\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * The property mapper
     *
     * @var \TYPO3\CMS\Extbase\Property\PropertyMapper
     * @inject
     */
    protected $propertyMapper;

    /**
     * The reflection service
     *
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     * @inject
     */
    protected $reflectionService;

    /**
     * @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder
     * @inject
     */
    protected $configurationBuilder;

    /**
     * The current depth when preparing model data for output
     *
     * @var int
     */
    protected $currentModelDataDepth = 0;

    /**
     * Logger instance
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * Dictionary of handled models to their count
     *
     * @var array
     */
    protected static $handledModels = array();

    /**
     * Returns the domain model repository class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getRepositoryClassForResourceType(ResourceType $resourceType)
    {
        list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType($resourceType);
        $repositoryClass = 'Tx_' . $extension . '_Domain_Repository_' . $model . 'Repository';
        if (!class_exists($repositoryClass)) {
            $repositoryClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Repository\\' . $model . 'Repository';
        }

        return $repositoryClass;
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
        /** @var \TYPO3\CMS\Extbase\Persistence\RepositoryInterface $repository */
        $repository = $this->objectManager->get($repositoryClass);
        $repository->setDefaultQuerySettings(
            $this->objectManager->get('Cundd\\Rest\\Persistence\\Generic\\RestQuerySettings')
        );

        return $repository;
    }

    /**
     * Returns the domain model class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getModelClassForResourceType(ResourceType $resourceType)
    {
        list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType($resourceType);
        $modelClass = 'Tx_' . $extension . '_Domain_Model_' . $model;
        if (!class_exists($modelClass)) {
            $modelClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Model\\' . $model;
        }

        return $modelClass;
    }

    /**
     * Returns all domain model for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return DomainObjectInterface[]|QueryResultInterface
     */
    public function getAllModelsForResourceType(ResourceType $resourceType)
    {
        return $this->getRepositoryForResourceType($resourceType)->findAll();
    }

    /**
     * Returns a domain model for the given API resource type and data
     * This method will load existing models.
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return DomainObjectInterface
     */
    public function getModelWithDataForResourceType($data, ResourceType $resourceType)
    {
        $modelClass = $this->getModelClassForResourceType($resourceType);

        // If no data is given return a new instance
        if (!$data) {
            return $this->getEmptyModelForResourceType($resourceType);
        } elseif (is_scalar($data)) { // If it is a scalar treat it as identity
            return $this->getModelWithIdentityForResourceType($data, $resourceType);
        }

        $data = $this->prepareModelData($data);
        try {
            return $this->propertyMapper->convert(
                $data,
                $modelClass,
                $this->getPropertyMappingConfigurationForResourceType($resourceType)
            );
        } catch (\TYPO3\CMS\Extbase\Property\Exception $exception) {
            $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }

        return null;
    }

    /**
     * Returns a domain model for the given API resource type and data
     * Even if the data contains an identifier, the existing model will not be loaded.
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return DomainObjectInterface
     */
    public function getNewModelWithDataForResourceType($data, ResourceType $resourceType)
    {
        $uid = null;
        // If no data is given return a new instance
        if (!$data) {
            return $this->getEmptyModelForResourceType($resourceType);
        }

        // Save the identifier and remove it from the data array
        if (isset($data['__identity']) && $data['__identity']) {
            // Load the UID of the existing model
            $uid = $this->getUidOfModelWithIdentityForResourceType($data['__identity'], $resourceType);
        } elseif (isset($data['uid']) && $data['uid']) {
            $uid = $data['uid'];
        }
        if ($uid) {
            unset($data['__identity']);
            unset($data['uid']);
        }

        // Get a fresh model
        $model = $this->getModelWithDataForResourceType($data, $resourceType);

        if ($model) {
            // Set the saved identifier
            $model->_setProperty('uid', $uid);
        }

        return $model;
    }

    /**
     * Returns a new domain model for the given API resource type
     *
     * @param ResourceType $resourceType
     * @return DomainObjectInterface
     */
    public function getModelForResourceType(ResourceType $resourceType)
    {
        return $this->getModelWithDataForResourceType(array(), $resourceType);
    }

    /**
     * Returns a new domain model for the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the model for
     * @return DomainObjectInterface
     */
    public function getEmptyModelForResourceType(ResourceType $resourceType)
    {
        return $this->objectManager->get($this->getModelClassForResourceType($resourceType));
    }

    /**
     * Returns the data for the given lazy object storage
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage $lazyObjectStorage
     * @param string                                                   $propertyKey
     * @param DomainObjectInterface                                    $model
     * @return array<mixed>
     */
    public function getModelDataFromLazyObjectStorage($lazyObjectStorage, $propertyKey, $model)
    {
        $returnData = null;
        // Get the first level of nested objects
        if ($this->currentModelDataDepth < 1) {
            $this->currentModelDataDepth++;
            $returnData = array();

            // Collect each object of the lazy object storage
            foreach ($lazyObjectStorage as $subObject) {
                $returnData[] = $this->getModelData($subObject);
            }
            $this->currentModelDataDepth--;
        } else {
            $returnData = $this->getUriToNestedResource($propertyKey, $model);
        }

        return $returnData;
    }

    /**
     * Returns the data for the given lazy object storage
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy $proxy
     * @param string                                                  $propertyKey
     * @param DomainObjectInterface                                   $model
     * @return array<mixed>
     */
    public function getModelDataFromLazyLoadingProxy($proxy, $propertyKey, $model)
    {
        $returnData = array();

        /*
         * Get the first level of nested objects and all built in TYPO3
         * categories
         */
        if ($this->currentModelDataDepth < 1 && $model instanceof \TYPO3\CMS\Extbase\Domain\Model\Category) {
            $this->currentModelDataDepth++;

            $returnData = $this->getModelData($proxy->_loadRealInstance());

            $this->currentModelDataDepth--;
        }

        return $returnData;
    }

    /**
     * Returns the URI of a nested resource
     *
     * @param string                $resourceKey
     * @param DomainObjectInterface $model
     * @return string
     */
    public function getUriToNestedResource($resourceKey, $model)
    {
        $currentUri = '/rest/' . Utility::getResourceTypeForClassName(get_class($model)) . '/' . $model->getUid() . '/';

        if ($resourceKey !== null) {
            $currentUri .= $resourceKey;
        }

        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);

        $protocol = ((!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? 'http' : 'https');

        return $protocol . '://' . $host . $currentUri;
    }

    /**
     * Returns the URI of a resource
     *
     * @param DomainObjectInterface $model
     * @return string
     */
    public function getUriToResource($model)
    {
        return $this->getUriToNestedResource(null, $model);
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
        $propertyValue = $model->_getProperty($propertyKey);
        if (is_object($propertyValue)) {
            if ($propertyValue instanceof LazyObjectStorage) {
                $propertyValue = iterator_to_array($propertyValue);

                // Transform objects recursive
                foreach ($propertyValue as $childPropertyKey => $childPropertyValue) {
                    if (is_object($childPropertyValue)) {
                        $propertyValue[$childPropertyKey] = $this->getModelData($childPropertyValue);
                    }
                }
                $propertyValue = array_values($propertyValue);
            } else {
                $propertyValue = $this->getModelData($propertyValue);
            }
        } elseif (!$propertyValue) {
            return null;
        }

        return $propertyValue;
    }

    /**
     * Adds or updates the given model in the repository for the
     * given API resource type
     *
     * @param DomainObjectInterface $model
     * @param ResourceType          $resourceType The API resource type
     * @return void
     */
    public function saveModelForResourceType($model, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($repository) {
            if ($model->_isNew()) {
                $repository->add($model);
            } else {
                $repository->update($model);
            }
            $this->persistAllChanges();
        }
    }

    /**
     * Tells the Data Provider to replace the given old model with the new one
     * in the repository for the given API resource type
     *
     * @param DomainObjectInterface $oldModel
     * @param DomainObjectInterface $newModel
     * @param ResourceType          $resourceType The API resource type
     * @return void
     */
    public function replaceModelForResourceType($oldModel, $newModel, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($repository) {
            $repository->update($newModel);
            $this->persistAllChanges();
        }
    }

    /**
     * Adds or updates the given model in the repository for the
     * given API resource type
     *
     * @param DomainObjectInterface $model
     * @param ResourceType          $resourceType The API resource type
     * @return void
     */
    public function removeModelForResourceType($model, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($repository) {
            $repository->remove($model);
            $this->persistAllChanges();
        }
    }

    /**
     * Persist all changes to the database
     */
    public function persistAllChanges()
    {
        /** @var PersistenceManagerInterface $persistenceManager */
        $persistenceManager = $this->objectManager->get(ObjectManager::getPersistenceManagerClassName());
        $persistenceManager->persistAll();
    }

    /**
     * Returns the UID of the model with the given identifier
     *
     * @param mixed        $identifier   The identifier
     * @param ResourceType $resourceType The resource type
     * @return int|null Returns the UID of NULL if the object couldn't be found
     */
    protected function getUidOfModelWithIdentityForResourceType($identifier, ResourceType $resourceType)
    {
        $model = $this->getModelWithIdentityForResourceType($identifier, $resourceType);
        if (!$model) {
            return null;
        }

        return $model->getUid();
    }

    /**
     * Returns the configuration for property mapping
     *
     * @param ResourceType|string $resourceType
     * @return \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration
     */
    protected function getPropertyMappingConfigurationForResourceType(ResourceType $resourceType)
    {
        return $this->configurationBuilder->build();
    }

    /**
     * Loads the model with the given identifier
     *
     * @param mixed               $identifier
     * @param ResourceType|string $resourceType
     * @return mixed|null|object
     */
    protected function getModelWithIdentityForResourceType($identifier, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);

        // Tries to fetch the object by UID
        $object = $repository->findByUid($identifier);
        if ($object) {
            return $object;
        }


        // Fetch the first identity property and search the repository for it
        $type = null;
        $property = null;
        try {
            $classSchema = $this->reflectionService->getClassSchema($this->getModelClassForResourceType($resourceType));
            $identityProperties = $classSchema->getIdentityProperties();

            $type = reset($identityProperties);
            $property = key($identityProperties);
        } catch (\Exception $exception) {
        }

        switch ($type) {
            case 'string':
                $typeMatching = is_string($identifier);
                break;

            case 'boolean':
                $typeMatching = is_bool($identifier);
                break;

            case 'integer':
                $typeMatching = is_int($identifier);
                break;

            case 'float':
                $typeMatching = is_float($identifier);
                break;

            case 'array':
            default:
                $typeMatching = false;
        }

        if ($typeMatching) {
            $findMethod = 'findOneBy' . ucfirst($property);

            return call_user_func(array($repository, $findMethod), $identifier);
        }

        return null;
    }

    /**
     * Prepares the given data before transforming it to a model
     *
     * @param $data
     * @return array
     */
    protected function prepareModelData($data)
    {
        return $data;
    }

    /**
     * Returns the logger
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * Returns the data from the given model
     *
     * @param DomainObjectInterface|object $model
     * @return array<mixed>
     */
    public function getModelData($model)
    {
        if (!is_object($model)) {
            return $model;
        }

        if ($model instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage && !method_exists($model, 'jsonSerialize')) {
            return $this->transformObjectStorage($model);
        }


        $modelHash = spl_object_hash($model);
        if (isset(static::$handledModels[$modelHash])) {
            static::$handledModels[$modelHash]++;
        } else {
            static::$handledModels[$modelHash] = 1;
        }

        if (static::$handledModels[$modelHash] < 2) {
            // Get the data from the model
            if (method_exists($model, 'jsonSerialize')) {
                $properties = $model->jsonSerialize();
            } elseif ($model instanceof FileInterface) {
                $properties = $this->transformFileReference($model);
            } elseif ($model instanceof AbstractFileFolder) {
                $properties = $this->transformFileReference($model->getOriginalResource());
            } elseif ($model instanceof DomainObjectInterface) {
                $properties = $model->_getProperties();
            } else {
                // Return the model directly
                $properties = $model;
            }

            if (is_array($properties)) {
                $properties = $this->transformProperties($model, $properties);

                $properties = $this->addClassProperty($model, $properties);
            }

            $result = $properties;
        } else {
            $result = $this->getUriToResource($model);
        }
        static::$handledModels[$modelHash]--;

        return $result;
    }

    /**
     * Transform the properties
     *
     * @param DomainObjectInterface|object $model
     * @param array                        $properties
     * @return array
     */
    protected function transformProperties($model, $properties)
    {
        // Transform objects recursive
        foreach ($properties as $propertyKey => $propertyValue) {
            if (is_object($propertyValue)) {
                $propertyValueHash = spl_object_hash($propertyValue);
                $modelRecursionCount = isset(static::$handledModels[$propertyValueHash])
                    ? static::$handledModels[$propertyValueHash]
                    : 0;

                if ($modelRecursionCount < 1) {
                    if ($propertyValue instanceof LazyLoadingProxy) {
                        $properties[$propertyKey] = $this->getModelDataFromLazyLoadingProxy(
                            $propertyValue,
                            $propertyKey,
                            $model
                        );
                    } elseif ($propertyValue instanceof LazyObjectStorage) {
                        $properties[$propertyKey] = $this->getModelDataFromLazyObjectStorage(
                            $propertyValue,
                            $propertyKey,
                            $model
                        );
                    } else {
                        $properties[$propertyKey] = $this->getModelData($propertyValue);
                    }
                } elseif (method_exists($propertyValue, 'getUid')) {
                    $properties[$propertyKey] = $this->getUriToNestedResource($propertyKey, $propertyValue);
                } else {
                    $properties[$propertyKey] = $propertyValue;
                }
            }
        }

        return $properties;
    }

    /**
     * Transform object storage
     *
     * @param \Traversable $objectStorage
     * @return array
     */
    protected function transformObjectStorage($objectStorage)
    {
        return array_values(array_map(array($this, 'getModelData'), iterator_to_array($objectStorage)));
    }

    /**
     * Retrieve data from a file reference
     *
     * @param \TYPO3\CMS\Core\Resource\ResourceInterface|Folder|\TYPO3\CMS\Core\Resource\AbstractFile $originalResource
     * @return array
     */
    protected function transformFileReference($originalResource)
    {
        static $depth = 0;
        if ($originalResource instanceof AbstractFileFolder) {
            if (++$depth > 10) {
                throw new \RuntimeException('Max nesting level');
            }
            $result = $this->transformFileReference($originalResource->getOriginalResource());
            $depth--;

            return $result;
        }

        try {
            if ($originalResource instanceof Folder) {
                $filesInFolder = array();
                foreach ($originalResource->getFiles() as $currentFile) {
                    $filesInFolder[] = $this->transformFileReference($currentFile);
                }

                return $filesInFolder;
            }

            if ($originalResource instanceof FileReference) {
                // This would expose all data
                // return $originalResource->getProperties();

                list($title, $description) = $this->getTitleAndDescription($originalResource);

                return array(
                    'uid'          => intval($originalResource->getReferenceProperty('uid_local')),
                    'referenceUid' => $originalResource->getUid(),
                    'name'         => $originalResource->getName(),
                    'mimeType'     => $originalResource->getMimeType(),
                    'url'          => $originalResource->getPublicUrl(),
                    'size'         => $originalResource->getSize(),
                    'title'        => $title,
                    'description'  => $description,
                );
            }

            if ($originalResource instanceof FileInterface) {
                return array(
                    'name'     => $originalResource->getName(),
                    'mimeType' => $originalResource->getMimeType(),
                    'url'      => $originalResource->getPublicUrl(),
                    'size'     => $originalResource->getSize(),
                );
            }

            return array(
                'name' => $originalResource->getName(),
            );
        } catch (\RuntimeException $exception) {
            return array(
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage(),
            );
        }
    }

    /**
     * Get the title and description of a File
     *
     * @param FileReference $fileReference
     * @return array
     */
    private function getTitleAndDescription(FileReference $fileReference)
    {
        $title = '';
        $description = '';
        try {
            $title = $fileReference->getTitle();
        } catch (\InvalidArgumentException $exception) {
            $message = 'An invalid argument for the title has been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }
        try {
            $description = $fileReference->getDescription();
        } catch (\InvalidArgumentException $exception) {
            $message = 'An invalid argument for the description has been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }

        return array($title, $description);
    }

    /**
     * @param mixed $model
     * @param array $properties
     * @return mixed
     */
    protected function addClassProperty($model, array $properties)
    {
        if (isset($properties['__class'])) {
            return $properties;
        }

        if (true === (bool)$this->objectManager->getConfigurationProvider()->getSetting('addClass', 0)) {
            $properties['__class'] = is_object($model) ? get_class($model) : gettype($model);
        }

        return $properties;
    }
}
