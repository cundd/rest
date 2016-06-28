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

/**
 * DataProvider instance
 *
 * @package Cundd\Rest\DataProvider
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
     * Returns the domain model repository class name for the given API path
     *
     * @param string $path API path to get the repository for
     * @return string
     */
    public function getRepositoryClassForPath($path)
    {
        list($vendor, $extension, $model) = Utility::getClassNamePartsForPath($path);
        $repositoryClass = 'Tx_' . $extension . '_Domain_Repository_' . $model . 'Repository';
        if (!class_exists($repositoryClass)) {
            $repositoryClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Repository\\' . $model . 'Repository';
        }
        return $repositoryClass;
    }

    /**
     * Returns the domain model repository for the models the given API path points to
     *
     * @param string $path API path to get the repository for
     * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
     */
    public function getRepositoryForPath($path)
    {
        $repositoryClass = $this->getRepositoryClassForPath($path);
        /** @var \TYPO3\CMS\Extbase\Persistence\RepositoryInterface $repository */
        $repository = $this->objectManager->get($repositoryClass);
        $repository->setDefaultQuerySettings($this->objectManager->get('Cundd\\Rest\\Persistence\\Generic\\RestQuerySettings'));
        return $repository;
    }

    /**
     * Returns the domain model class name for the given API path
     *
     * @param string $path API path to get the repository for
     * @return string
     */
    public function getModelClassForPath($path)
    {
        list($vendor, $extension, $model) = Utility::getClassNamePartsForPath($path);
        $modelClass = 'Tx_' . $extension . '_Domain_Model_' . $model;
        if (!class_exists($modelClass)) {
            $modelClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Model\\' . $model;
        }
        return $modelClass;
    }

    /**
     * Returns all domain model for the given API path
     *
     * @param string $path API path to get the repository for
     * @return DomainObjectInterface
     */
    public function getAllModelsForPath($path)
    {
        return $this->getRepositoryForPath($path)->findAll();
    }

    /**
     * Returns a domain model for the given API path and data
     * This method will load existing models.
     *
     * @param array|string|int $data Data of the new model or it's UID
     * @param string $path API path to get the repository for
     * @return DomainObjectInterface
     */
    public function getModelWithDataForPath($data, $path)
    {
        $modelClass = $this->getModelClassForPath($path);

        // If no data is given return a new instance
        if (!$data) {
            return $this->getEmptyModelForPath($path);
        } elseif (is_scalar($data)) { // If it is a scalar treat it as identity
            return $this->getModelWithIdentityForPath($data, $path);
        }

        $data = $this->prepareModelData($data);
        try {
            $model = $this->propertyMapper->convert($data, $modelClass);
        } catch (\TYPO3\CMS\Extbase\Property\Exception $exception) {
            $model = null;

            $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }
        return $model;
    }

    /**
     * Returns a domain model for the given API path and data
     * Even if the data contains an identifier, the existing model will not be loaded.
     *
     * @param array|string|int $data Data of the new model or it's UID
     * @param string $path API path to get the repository for
     * @return DomainObjectInterface
     */
    public function getNewModelWithDataForPath($data, $path)
    {
        $uid = null;
        // If no data is given return a new instance
        if (!$data) {
            return $this->getEmptyModelForPath($path);
        }

        // Save the identifier and remove it from the data array
        if (isset($data['__identity']) && $data['__identity']) {
            // Load the UID of the existing model
            $uid = $this->getUidOfModelWithIdentityForPath($data['__identity'], $path);
        } elseif (isset($data['uid']) && $data['uid']) {
            $uid = $data['uid'];
        }
        if ($uid) {
            unset($data['__identity']);
            unset($data['uid']);
        }

        // Get a fresh model
        $model = $this->getModelWithDataForPath($data, $path);

        if ($model) {
            // Set the saved identifier
            $model->_setProperty('uid', $uid);
        }
        return $model;
    }

    /**
     * Returns a new domain model for the given API path
     *
     * @param string $path
     * @return DomainObjectInterface
     */
    public function getModelForPath($path)
    {
        return $this->getModelWithDataForPath(array(), $path);
    }

    /**
     * Returns a new domain model for the given API path points to
     *
     * @param string $path API path to get the model for
     * @return DomainObjectInterface
     */
    public function getEmptyModelForPath($path)
    {
        $modelClass = $this->getModelClassForPath($path);
        return $this->objectManager->get($modelClass);
    }

    /**
     * Returns the data for the given lazy object storage
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage $lazyObjectStorage
     * @param string $propertyKey
     * @param DomainObjectInterface $model
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
     * @param string $propertyKey
     * @param DomainObjectInterface $model
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
     * @param string $resourceKey
     * @param DomainObjectInterface $model
     * @return string
     */
    public function getUriToNestedResource($resourceKey, $model)
    {
        $currentUri = '/rest/';
        $currentUri .= Utility::getPathForClassName(get_class($model)) . '/' . $model->getUid() . '/';

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
     * @param string $propertyKey
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
     * given API path
     *
     * @param DomainObjectInterface $model
     * @param string $path The API path
     * @return void
     */
    public function saveModelForPath($model, $path)
    {
        $repository = $this->getRepositoryForPath($path);
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
     * in the repository for the given API path
     *
     * @param DomainObjectInterface $oldModel
     * @param DomainObjectInterface $newModel
     * @param string $path The API path
     * @return void
     */
    public function replaceModelForPath($oldModel, $newModel, $path)
    {
        $repository = $this->getRepositoryForPath($path);
        if ($repository) {
            $repository->update($newModel);
            $this->persistAllChanges();
        }
    }

    /**
     * Adds or updates the given model in the repository for the
     * given API path
     *
     * @param DomainObjectInterface $model
     * @param string $path The API path
     * @return void
     */
    public function removeModelForPath($model, $path)
    {
        $repository = $this->getRepositoryForPath($path);
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
     * @param mixed $identifier The identifier
     * @param string $path The path
     * @return integer|null    Returns the UID of NULL if the object couldn't be found
     */
    protected function getUidOfModelWithIdentityForPath($identifier, $path)
    {
        $model = $this->getModelWithIdentityForPath($identifier, $path);
        if (!$model) {
            return null;
        }
        return $model->getUid();
    }

    /**
     * Loads the model with the given identifier
     *
     * @param mixed $identifier The identifier
     * @param string $path The path
     * @return mixed|null|object
     */
    protected function getModelWithIdentityForPath($identifier, $path)
    {
        $repository = $this->getRepositoryForPath($path);

        // Tries to fetch the object by UID
        $object = $repository->findByUid($identifier);
        if ($object) {
            return $object;
        }


        // Fetch the first identity property and search the repository for it
        $type = null;
        $property = null;
        try {
            $classSchema = $this->reflectionService->getClassSchema($this->getModelClassForPath($path));
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

                if (!isset($properties['__class'])
                    && false === (bool)$this->objectManager->getConfigurationProvider()->getSetting('doNotAddClass', 0)
                ) {
                    $properties['__class'] = get_class($model);
                }
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
     * @param array $properties
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
                        $properties[$propertyKey] = $this->getModelDataFromLazyLoadingProxy($propertyValue, $propertyKey, $model);
                    } elseif ($propertyValue instanceof LazyObjectStorage) {
                        $properties[$propertyKey] = $this->getModelDataFromLazyObjectStorage($propertyValue, $propertyKey, $model);
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
                    'name'        => $originalResource->getName(),
                    'mimeType'    => $originalResource->getMimeType(),
                    'url'         => $originalResource->getPublicUrl(),
                    'size'        => $originalResource->getSize(),
                    'title'       => $title,
                    'description' => $description,
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
            return array();
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
            $message = 'An invalid argument for the title have been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }
        try {
            $description = $fileReference->getDescription();
        } catch (\InvalidArgumentException $exception) {
            $message = 'An invalid argument for the description have been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }
        return array($title, $description);
    }
}
