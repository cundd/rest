<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Exception\ClassLoadingException;
use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Exception\InvalidPropertyException;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Persistence\Generic\RestQuerySettings;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\SingletonInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;
use TYPO3\CMS\Extbase\Property\Exception as ExtbaseException;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;

use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Data Provider implementation for Extbase based Models
 */
class DataProvider implements DataProviderInterface, ClassLoadingInterface, SingletonInterface
{
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        protected readonly ExtractorInterface $extractor,
        protected readonly IdentityProviderInterface $identityProvider,
        protected ?LoggerInterface $logger = null,
    ) {
    }

    public function getModelData(RestRequestInterface $request, mixed $model): mixed
    {
        return $this->extractor->extract($request->getUri(), $model);
    }

    public function getRepositoryClassForResourceType(
        ResourceType $resourceType,
    ): string {
        [$vendor, $extension, $model] = Utility::getClassNamePartsForResourceType(
            $resourceType
        );

        return ($vendor ? $vendor . '\\' : '')
            . $extension
            . '\\Domain\\Repository\\'
            . $model . 'Repository';
    }

    /**
     * @return RepositoryInterface<object>
     */
    public function getRepositoryForResourceType(ResourceType $resourceType): object
    {
        /** @var class-string<object> $repositoryClass */
        $repositoryClass = $this->getRepositoryClassForResourceType($resourceType);
        $repository = null;
        $exception = null;
        try {
            /** @var RepositoryInterface<object> $repository */
            $repository = $this->objectManager->get($repositoryClass);
        } catch (Exception $exception) {
        }
        if (!$repository) {
            $triedClasses = sprintf(
                'Tried the following classes: "%s"',
                $repositoryClass
            );
            if ($exception) {
                $message = sprintf(
                    'Repository for resource type "%s" could not be created: %s %s',
                    $resourceType,
                    $exception->getMessage(),
                    $triedClasses
                );
                throw new ClassLoadingException($message, 1542116783, $exception);
            }
            $message = sprintf(
                'Repository for resource type "%s" could not be found. %s',
                $resourceType,
                $triedClasses
            );
            throw new ClassLoadingException($message, 1542116782);
        }
        /** @var QuerySettingsInterface $defaultQuerySettings */
        $defaultQuerySettings = $this->objectManager->get(RestQuerySettings::class);
        $repository->setDefaultQuerySettings($defaultQuerySettings);

        return $repository;
    }

    /**
     * @return class-string<object>|string
     */
    public function getModelClassForResourceType(ResourceType $resourceType): string
    {
        $modelEntityForResourceType = Utility::getModelEntityForResourceType(
            $resourceType
        );
        if ($modelEntityForResourceType && class_exists($modelEntityForResourceType)) {
            return $modelEntityForResourceType;
        }

        return '';
    }

    public function fetchAllModels(RestRequestInterface $request): iterable
    {
        return $this->getRepositoryForResourceType(
            $request->getResourceType(),
        )->findAll();
    }

    public function countAllModels(RestRequestInterface $request): int
    {
        return $this->getRepositoryForResourceType(
            $request->getResourceType(),
        )->countAll();
    }

    public function fetchModel(
        RestRequestInterface $request,
        int|array|string $identifier,
    ): ?object {
        if ($identifier && is_scalar($identifier)) { // If it is a scalar treat it as identity
            return $this->getModelWithIdentityForResourceType(
                $identifier,
                $request->getResourceType(),
            );
        }

        return null;
    }

    public function createModel(RestRequestInterface $request, array $data): ?object
    {
        // If no data is given return a new empty instance
        if (!$data) {
            return $this->getEmptyModelForResourceType(
                $request->getResourceType(),
            );
        }

        // It is **not** allowed to insert Models with a defined UID
        if (isset($data['__identity']) && $data['__identity']) {
            return new InvalidPropertyException('Invalid property "__identity"');
        } elseif (isset($data['uid']) && $data['uid']) {
            return new InvalidPropertyException('Invalid property "uid"');
        }

        // Get a fresh model
        return $this->convertIntoModel($request, $data);
    }

    /**
     * @param non-empty-string $propertyParameter
     */
    public function getModelProperty(
        RestRequestInterface $request,
        object $model,
        string $propertyParameter,
    ): mixed {
        InvalidArgumentException::assertObject($model);
        $propertyKey = $this->convertPropertyParameterToKey($propertyParameter);

        $normalizedGetter = 'get' . ucfirst($propertyKey);
        if (method_exists($model, $normalizedGetter) && is_callable([$model, $normalizedGetter])) {
            return $this->getModelData($request, $model->$normalizedGetter());
        }

        $getter = 'get' . ucfirst($propertyParameter);
        if (method_exists($model, $getter) && is_callable([$model, $getter])) {
            return $this->getModelData($request, $model->$getter());
        }

        if ($model instanceof DomainObjectInterface) {
            $value = $model->_getProperty($propertyKey);
            if (null !== $value) {
                return $this->getModelData($request, $value);
            }

            return $this->getModelData(
                $request,
                $model->_getProperty($propertyParameter)
            );
        }

        return null;
    }

    public function saveModel(RestRequestInterface $request, object $model): void
    {
        $repository = $this->getRepositoryForResourceType(
            $request->getResourceType()
        );
        if ($this->isModelNew($model)) {
            $repository->add($model);
        } else {
            $repository->update($model);
        }
        $this->persistAllChanges();
    }

    public function updateModel(
        object $updatedModel,
        ResourceType $resourceType,
    ): void {
        $repository = $this->getRepositoryForResourceType($resourceType);
        $repository->update($updatedModel);
        $this->persistAllChanges();
    }

    public function removeModel(
        RestRequestInterface $request,
        object $model,
    ): void {
        $repository = $this->getRepositoryForResourceType(
            $request->getResourceType(),
        );
        $repository->remove($model);
        $this->persistAllChanges();
    }

    public function convertIntoModel(
        RestRequestInterface $request,
        array $data,
    ): ?object {
        $resourceType = $request->getResourceType();
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

    public function getEmptyModelForResourceType(
        ResourceType $resourceType,
    ): object {
        /** @var class-string<object> $modelClassForResourceType */
        $modelClassForResourceType = $this->getModelClassForResourceType($resourceType);
        if ($this->objectManager->has($modelClassForResourceType)) {
            return $this->objectManager->get($modelClassForResourceType);
        }

        return new $modelClassForResourceType();
    }

    /**
     * Persist all changes to the database
     */
    protected function persistAllChanges(): void
    {
        $persistenceManager = $this->objectManager->get(
            PersistenceManagerInterface::class
        );
        $persistenceManager->persistAll();
    }

    /**
     * Return the UID of the model with the given identifier
     *
     * @param mixed        $identifier   The identifier
     * @param ResourceType $resourceType The resource type
     *
     * @return int|string|null Returns the UID or NULL if the object couldn't be found
     */
    protected function getUidOfModelWithIdentityForResourceType(
        mixed $identifier,
        ResourceType $resourceType,
    ): int|string|null {
        $model = $this->getModelWithIdentityForResourceType(
            $identifier,
            $resourceType
        );
        if ($model && is_callable([$model, 'getUid'])) {
            return $model->getUid();
        } else {
            return null;
        }
    }

    /**
     * Convert incoming property parameter names into property keys
     *
     * Example:
     *  'dog-name' => 'dogName'
     *
     * @param non-empty-string $propertyParameter
     *
     * @return non-empty-string
     */
    protected function convertPropertyParameterToKey(
        string $propertyParameter,
    ): string {
        $key = str_replace(' ', '', ucwords(str_replace(
            ['_', '-'],
            ' ',
            $propertyParameter
        )));
        assert('' !== $key);

        return $key;
    }

    /**
     * Return the configuration for property mapping
     *
     * @return PropertyMappingConfiguration
     */
    protected function getPropertyMappingConfigurationForResourceType(
        /* @noinspection PhpUnusedParameterInspection */
        ResourceType $resourceType,
    ): object {
        return $this->objectManager
            ->get(PropertyMappingConfigurationBuilder::class)
            ->build();
    }

    /**
     * Load the model with the given identifier
     *
     * @return DomainObjectInterface|object|null
     */
    protected function getModelWithIdentityForResourceType(
        mixed $identifier,
        ResourceType $resourceType,
    ): ?object {
        $repository = $this->getRepositoryForResourceType($resourceType);

        // Tries to fetch the object by UID
        $object = $repository->findByUid($identifier);
        if ($object) {
            return $object;
        }

        [$property, $type] = $this->identityProvider->getIdentityProperty(
            $this->getModelClassForResourceType($resourceType)
        );

        $typeMatching = match ($type) {
            'string'  => is_string($identifier),
            'boolean' => is_bool($identifier),
            'integer' => is_int($identifier),
            'float'   => is_float($identifier),
            default   => false,
        };

        if ($typeMatching && $property && $repository instanceof Repository) {
            return $repository->findOneBy([$property => $identifier]);
        }

        return null;
    }

    /**
     * Prepares the given data before transforming it to a model
     *
     * @param array<string,mixed> $data
     *
     * @return array<string,mixed>
     */
    protected function prepareModelData(array $data): array
    {
        return $data;
    }

    /**
     * Return the logger
     */
    protected function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)
                ->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    protected function logException(Exception $exception): void
    {
        $message = 'Uncaught exception #'
            . $exception->getCode() . ': ' . $exception->getMessage();
        $this->getLogger()->log(
            LogLevel::ERROR,
            $message,
            ['exception' => $exception]
        );
    }

    /**
     * Return if the given instance is not yet stored in the database
     *
     * @param object|DomainObjectInterface $model
     */
    protected function isModelNew(object $model): bool
    {
        if ($model instanceof DomainObjectInterface) {
            return $model->_isNew();
        }
        if (is_callable([$model, 'getUid'])) {
            return null === $model->getUid();
        }

        return true;
    }
}
