<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Exception\ClassLoadingException;
use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Exception\InvalidPropertyException;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Persistence\Generic\RestQuerySettings;
use Cundd\Rest\SingletonInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;
use TYPO3\CMS\Extbase\Property\Exception as ExtbaseException;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;

use function sprintf;

/**
 * Data Provider implementation for Extbase based Models
 */
class DataProvider implements DataProviderInterface, ClassLoadingInterface, SingletonInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ExtractorInterface
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
     * @param LoggerInterface|null      $logger
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

    public function getRepositoryClassForResourceType(ResourceType $resourceType): string
    {
        [$vendor, $extension, $model] = Utility::getClassNamePartsForResourceType($resourceType);

        return ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Repository\\' . $model . 'Repository';
    }

    public function getRepositoryForResourceType(ResourceType $resourceType)
    {
        $repositoryClass = $this->getRepositoryClassForResourceType($resourceType);
        $repository = null;
        $exception = null;
        /** @var RepositoryInterface|null $repository */
        try {
            $repository = $this->objectManager->get($repositoryClass);
        } catch (Exception $exception) {
        }
        if (!$repository) {
            $triedClasses = sprintf('Tried the following classes: "%s"', $repositoryClass);
            if ($exception) {
                $message = sprintf(
                    'Repository for resource type "%s" could not be created: %s %s',
                    $resourceType,
                    $exception->getMessage(),
                    $triedClasses
                );
                throw new ClassLoadingException($message, 1542116783, $exception);
            } else {
                $message = sprintf(
                    'Repository for resource type "%s" could not be found. %s',
                    $resourceType,
                    $triedClasses
                );
                throw new ClassLoadingException($message, 1542116782);
            }
        }
        /** @var QuerySettingsInterface $defaultQuerySettings */
        $defaultQuerySettings = $this->objectManager->get(RestQuerySettings::class);
        $repository->setDefaultQuerySettings($defaultQuerySettings);

        return $repository;
    }

    public function getModelClassForResourceType(ResourceType $resourceType): string
    {
        $modelEntityForResourceType = Utility::getModelEntityForResourceType($resourceType);
        if ($modelEntityForResourceType && class_exists($modelEntityForResourceType)) {
            return $modelEntityForResourceType;
        }

        return '';
    }

    public function fetchAllModels(ResourceType $resourceType): iterable
    {
        return $this->getRepositoryForResourceType($resourceType)->findAll();
    }

    public function countAllModels(ResourceType $resourceType): int
    {
        return $this->getRepositoryForResourceType($resourceType)->countAll();
    }

    public function fetchModel($identifier, ResourceType $resourceType): ?object
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
            return new InvalidPropertyException('Invalid property "__identity"');
        } elseif (isset($data['uid']) && $data['uid']) {
            return new InvalidPropertyException('Invalid property "uid"');
        }

        // Get a fresh model
        return $this->convertIntoModel($data, $resourceType);
    }

    public function getModelProperty(object $model, string $propertyParameter)
    {
        InvalidArgumentException::assertObject($model);
        $propertyKey = $this->convertPropertyParameterToKey($propertyParameter);

        $normalizedGetter = 'get' . ucfirst($propertyKey);
        if (method_exists($model, $normalizedGetter) && is_callable([$model, $normalizedGetter])) {
            return $this->getModelData($model->$normalizedGetter());
        }

        $getter = 'get' . ucfirst($propertyParameter);
        if (method_exists($model, $getter) && is_callable([$model, $getter])) {
            return $this->getModelData($model->$getter());
        }

        if ($model instanceof DomainObjectInterface) {
            $value = $model->_getProperty($propertyKey);
            if (null !== $value) {
                return $this->getModelData($value);
            } else {
                return $this->getModelData($model->_getProperty($propertyParameter));
            }
        }

        return null;
    }

    public function saveModel(object $model, ResourceType $resourceType): void
    {
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($this->isModelNew($model)) {
            $repository->add($model);
        } else {
            $repository->update($model);
        }
        $this->persistAllChanges();
    }

    /**
     * @param object       $updatedModel
     * @param ResourceType $resourceType
     */
    public function updateModel(
        object $updatedModel,
        ResourceType $resourceType
    ): void {
        $repository = $this->getRepositoryForResourceType($resourceType);
        $repository->update($updatedModel);
        $this->persistAllChanges();
    }

    public function removeModel(object $model, ResourceType $resourceType): void
    {
        InvalidArgumentException::assertObjectOrNull($model);
        $repository = $this->getRepositoryForResourceType($resourceType);
        $repository->remove($model);
        $this->persistAllChanges();
    }

    public function convertIntoModel(array $data, ResourceType $resourceType): ?object
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
    protected function persistAllChanges(): void
    {
        $persistenceManager = $this->objectManager->get(PersistenceManagerInterface::class);
        $persistenceManager->persistAll();
    }

    /**
     * Return the UID of the model with the given identifier
     *
     * @param mixed        $identifier   The identifier
     * @param ResourceType $resourceType The resource type
     * @return int|string|null Returns the UID or NULL if the object couldn't be found
     */
    protected function getUidOfModelWithIdentityForResourceType($identifier, ResourceType $resourceType)
    {
        $model = $this->getModelWithIdentityForResourceType($identifier, $resourceType);

        return $model ? $model->getUid() : null;
    }

    /**
     * Convert incoming property parameter names into property keys
     *
     * Example:
     *  'dog-name' => 'dogName'
     *
     * @param string $propertyParameter
     * @return string
     */
    protected function convertPropertyParameterToKey(string $propertyParameter): string
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $propertyParameter)));
    }

    /**
     * Return the configuration for property mapping
     *
     * @param ResourceType|string $resourceType
     * @return PropertyMappingConfiguration
     */
    protected function getPropertyMappingConfigurationForResourceType(
        /** @noinspection PhpUnusedParameterInspection */
        ResourceType $resourceType
    ): object {
        return $this->objectManager->get(PropertyMappingConfigurationBuilder::class)->build();
    }

    /**
     * Load the model with the given identifier
     *
     * @param mixed               $identifier
     * @param ResourceType|string $resourceType
     * @return null|object
     */
    protected function getModelWithIdentityForResourceType($identifier, ResourceType $resourceType): ?object
    {
        $repository = $this->getRepositoryForResourceType($resourceType);

        // Tries to fetch the object by UID
        $object = $repository->findByUid($identifier);
        if ($object) {
            return $object;
        }

        [$property, $type] = $this->identityProvider->getIdentityProperty(
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
     * @param array $data
     * @return array
     */
    protected function prepareModelData(array $data): array
    {
        return $data;
    }

    /**
     * Returns the logger
     *
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * @param $exception
     */
    protected function logException(Exception $exception)
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
    protected function isModelNew(object $model): bool
    {
        if ($model instanceof DomainObjectInterface) {
            return $model->_isNew();
        }
        if (is_callable([$model, 'getUid'])) {
            return $model->getUid() === null;
        }

        return true;
    }
}
