<?php


namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\Persistence\Repository;
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
    protected $objectConverterMap = [];

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
            $objectConverter = $this->objectManager->get(ObjectConverter::class);
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
        return Repository::class;
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
     * @return object|DomainObjectInterface|VirtualObject
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
            $this->getLogger()->log(LogLevel::ERROR, $message, ['exception' => $exception]);
        }

        return $model;
    }

    /**
     * Returns a new domain model for the given API resource type points to
     *
     * @param ResourceType $resourceType API resource type to get the model for
     * @return object|VirtualObject
     */
    public function getEmptyModelForResourceType(ResourceType $resourceType)
    {
        return new VirtualObject();
    }

    /**
     * Returns the data from the given model
     *
     * @param object|DomainObjectInterface $model
     * @return array
     */
    public function getModelData($model)
    {
        $properties = parent::getModelData($model);
        if ($properties === $model) {
            return [];
        }

        return $properties;
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
     * @param object|DomainObjectInterface $model
     * @param ResourceType                 $resourceType The API resource type
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
