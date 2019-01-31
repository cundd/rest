<?php
declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Persistence\Generic\RestQuerySettings;
use Cundd\Rest\SingletonInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Property\Exception as ExtbaseException;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;

/**
 * Data Provider implementation for Extbase based Models
 */
class DataProvider implements DataProviderInterface, ClassLoadingInterface, SingletonInterface
{
    /**
     * @var \Cundd\Rest\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Cundd\Rest\DataProvider\ExtractorInterface
     */
    protected $extractor;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * Data Provider constructor
     *
     * @param ObjectManagerInterface    $objectManager
     * @param ExtractorInterface        $extractor
     * @param IdentityProviderInterface $identityProvider
     * @param LoggerInterface           $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ExtractorInterface $extractor,
        IdentityProviderInterface $identityProvider,
        LoggerInterface $logger = null
    ) {
        $this->objectManager = $objectManager;
        $this->extractor = $extractor;
        $this->logger = $logger;
        $this->identityProvider = $identityProvider;
    }

    public function getModelData($model)
    {
        return $this->extractor->extract($model);
    }

    public function getRepositoryClassForResourceType(ResourceType $resourceType)
    {
        list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType($resourceType);
        $repositoryClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Repository\\' . $model . 'Repository';
        if (!class_exists($repositoryClass)) {
            $repositoryClass = 'Tx_' . $extension . '_Domain_Repository_' . $model . 'Repository';
        }

        return $repositoryClass;
    }

    public function getRepositoryForResourceType(ResourceType $resourceType)
    {
        $repositoryClass = $this->getRepositoryClassForResourceType($resourceType);
        $repository = null;
        $exception = null;
        /** @var \TYPO3\CMS\Extbase\Persistence\RepositoryInterface $repository */
        try {
            $repository = $this->objectManager->get($repositoryClass);
        } catch (\Exception $exception) {
        }
        if (!$repository) {
            list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType($resourceType);
            $message = sprintf(
                'Repository for resource type "%s" could not be found. Tried "%s" and "%s"',
                $resourceType,
                ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Repository\\' . $model . 'Repository',
                'Tx_' . $extension . '_Domain_Repository_' . $model . 'Repository'
            );

            if ($exception) {
                throw new \LogicException($message . ': ' . $exception->getMessage(), 1542116783, $exception);
            } else {
                throw new \LogicException($message, 1542116782);
            }
        }
        /** @var QuerySettingsInterface $defaultQuerySettings */
        $defaultQuerySettings = $this->objectManager->get(RestQuerySettings::class);
        $repository->setDefaultQuerySettings($defaultQuerySettings);

        return $repository;
    }

    public function getModelClassForResourceType(ResourceType $resourceType)
    {
        list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType($resourceType);
        $modelClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Model\\' . $model;
        if (!class_exists($modelClass)) {
            $modelClass = 'Tx_' . $extension . '_Domain_Model_' . $model;
        }

        return $modelClass;
    }

    public function fetchAllModels(ResourceType $resourceType)
    {
        return $this->getRepositoryForResourceType($resourceType)->findAll();
    }

    public function countAllModels(ResourceType $resourceType)
    {
        return $this->getRepositoryForResourceType($resourceType)->countAll();
    }

    public function fetchModel($identifier, ResourceType $resourceType)
    {
        if ($identifier && is_scalar($identifier)) { // If it is a scalar treat it as identity
            return $this->getModelWithIdentityForResourceType($identifier, $resourceType);
        }

        return null;
    }

    public function createModel(array $data, ResourceType $resourceType)
    {
        // If no data is given return a new empty instance
        if (!$data) {
            return $this->getEmptyModelForResourceType($resourceType);
        }

        // It is **not** allowed to insert Models with a defined UID
        if (isset($data['__identity']) && $data['__identity']) {
            return new \UnexpectedValueException('Invalid property "__identity"');
        } elseif (isset($data['uid']) && $data['uid']) {
            return new \UnexpectedValueException('Invalid property "uid"');
        }

        // Get a fresh model
        $model = $this->convertIntoModel($data, $resourceType);

        return $model;
    }

    public function getModelProperty($model, $propertyKey)
    {
        if ($model instanceof DomainObjectInterface) {
            return $this->getModelData($model->_getProperty($propertyKey));
        }

        $getter = 'get' . ucfirst($propertyKey);
        if (is_callable([$model, $getter])) {
            return $this->getModelData($model->$getter());
        }

        return null;
    }

    public function saveModel($model, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($this->isModelNew($model)) {
            $repository->add($model);
        } else {
            $repository->update($model);
        }
        $this->persistAllChanges();
    }

    public function updateModel($updatedModel, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);
        $repository->update($updatedModel);
        $this->persistAllChanges();
    }

    public function removeModel($model, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);
        $repository->remove($model);
        $this->persistAllChanges();
    }

    public function convertIntoModel(array $data, ResourceType $resourceType)
    {
        $propertyMapper = $this->objectManager->get(PropertyMapper::class);
        try {
            return $propertyMapper->convert(
                $this->prepareModelData($data),
                $this->getModelClassForResourceType($resourceType),
                $this->getPropertyMappingConfigurationForResourceType($resourceType)
            );
        } catch (ExtbaseException $exception) {
            $this->logException($exception);

            return null;
        }
    }

    public function getEmptyModelForResourceType(ResourceType $resourceType)
    {
        return $this->objectManager->get($this->getModelClassForResourceType($resourceType));
    }

    /**
     * Persist all changes to the database
     */
    protected function persistAllChanges()
    {
        $persistenceManager = $this->objectManager->get(PersistenceManagerInterface::class);
        $persistenceManager->persistAll();
    }

    /**
     * Return the UID of the model with the given identifier
     *
     * @param mixed        $identifier   The identifier
     * @param ResourceType $resourceType The resource type
     * @return int|null Returns the UID or NULL if the object couldn't be found
     */
    protected function getUidOfModelWithIdentityForResourceType($identifier, ResourceType $resourceType)
    {
        $model = $this->getModelWithIdentityForResourceType($identifier, $resourceType);

        return $model ? $model->getUid() : null;
    }

    /**
     * Return the configuration for property mapping
     *
     * @param ResourceType|string $resourceType
     * @return \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration
     */
    protected function getPropertyMappingConfigurationForResourceType(
        /** @noinspection PhpUnusedParameterInspection */
        ResourceType $resourceType
    ) {
        return $this->objectManager->get(PropertyMappingConfigurationBuilder::class)->build();
    }

    /**
     * Load the model with the given identifier
     *
     * @param mixed               $identifier
     * @param ResourceType|string $resourceType
     * @return null|object
     */
    protected function getModelWithIdentityForResourceType($identifier, ResourceType $resourceType)
    {
        $repository = $this->getRepositoryForResourceType($resourceType);

        // Tries to fetch the object by UID
        $object = $repository->findByUid($identifier);
        if ($object) {
            return $object;
        }

        list($property, $type) = $this->identityProvider->getIdentityProperty(
            $this->getModelClassForResourceType($resourceType)
        );

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
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * @param $exception
     */
    protected function logException(\Exception $exception)
    {
        $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
        $this->getLogger()->log(LogLevel::ERROR, $message, ['exception' => $exception]);
    }

    /**
     * Return if the given instance is not yet stored in the database
     *
     * @param object|DomainObjectInterface $model
     * @return bool
     */
    protected function isModelNew($model)
    {
        if ($model instanceof DomainObjectInterface) {
            return $model->_isNew();
        }
        if (is_callable($model, 'getUid')) {
            return $model->getUid() === null;
        }

        return true;
    }
}
