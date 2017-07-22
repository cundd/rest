<?php

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Persistence\Generic\RestQuerySettings;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Property\Exception as ExtbaseException;

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
     * @var \Cundd\Rest\DataProvider\ExtractorInterface
     * @inject
     */
    protected $extractor;

    /**
     * Logger instance
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * Returns the data from the given model
     *
     * @param object|DomainObjectInterface $model
     * @return array
     */
    public function getModelData($model)
    {
        return $this->extractor->extract($model);
    }

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
        /** @var QuerySettingsInterface $defaultQuerySettings */
        $defaultQuerySettings = $this->objectManager->get(RestQuerySettings::class);
        $repository->setDefaultQuerySettings($defaultQuerySettings);

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
     * @return object[]|DomainObjectInterface[]|QueryResultInterface
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
     * @return object
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
        } catch (ExtbaseException $exception) {
            $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
            $this->getLogger()->log(LogLevel::ERROR, $message, ['exception' => $exception]);
        }

        return null;
    }

    /**
     * Returns a domain model for the given API resource type and data
     * Even if the data contains an identifier, the existing model will not be loaded.
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return object|DomainObjectInterface
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
     * @return object|DomainObjectInterface
     */
    public function getModelForResourceType(ResourceType $resourceType)
    {
        return $this->getModelWithDataForResourceType([], $resourceType);
    }

    /**
     * Returns a new domain model for the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the model for
     * @return object
     */
    public function getEmptyModelForResourceType(ResourceType $resourceType)
    {
        return $this->objectManager->get($this->getModelClassForResourceType($resourceType));
    }

    /**
     * Returns the property data from the given model
     *
     * @param object|DomainObjectInterface $model
     * @param string                       $propertyKey
     * @return mixed
     */
    public function getModelProperty($model, $propertyKey)
    {
        return $this->getModelData($model->_getProperty($propertyKey));
    }

    /**
     * Adds or updates the given model in the repository for the
     * given API resource type
     *
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
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
     * @param object|DomainObjectInterface $oldModel
     * @param object|DomainObjectInterface $newModel
     * @param ResourceType                 $resourceType The API resource type
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
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
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

            return call_user_func([$repository, $findMethod], $identifier);
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
}
