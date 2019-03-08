<?php
declare(strict_types=1);


namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\InvalidPropertyException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\Persistence\Repository;
use Cundd\Rest\VirtualObject\Persistence\RepositoryInterface;
use Cundd\Rest\VirtualObject\VirtualObject;

/**
 * Data Provider for Virtual Objects
 */
class VirtualObjectDataProvider extends DataProvider
{
    /**
     * @var ObjectConverter[]
     */
    protected $objectConverterMap = [];

    /**
     * @var \Cundd\Rest\VirtualObject\ConfigurationFactory
     * @inject
     */
    protected $configurationFactory;

    /**
     * Return the Object Converter with the matching configuration
     *
     * @param ResourceType|string $resourceType
     * @return ObjectConverter
     */
    public function getObjectConverterForResourceType(ResourceType $resourceType)
    {
        $resourceTypeString = (string)$resourceType;
        if (!isset($this->objectConverterMap[$resourceTypeString])) {
            $objectConverter = $this->objectManager->get(ObjectConverter::class);
            $objectConverter->setConfiguration($this->getConfigurationForResourceType($resourceType));

            $this->objectConverterMap[$resourceTypeString] = $objectConverter;

            return $objectConverter;
        }

        return $this->objectConverterMap[$resourceTypeString];
    }

    /**
     * Return the Configuration for the given resource type
     *
     * @param ResourceType $resourceType
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException
     * @return ConfigurationInterface
     */
    public function getConfigurationForResourceType(ResourceType $resourceType)
    {
        $resourceTypeString = (string)$resourceType;
        $virtualResourceTypeString = substr(
            $resourceTypeString,
            strpos($resourceTypeString, '-') + 1
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

    public function createModel(array $data, ResourceType $resourceType)
    {
        // If no data is given return a new empty instance
        if (!$data) {
            return $this->getEmptyModelForResourceType($resourceType);
        }

        return $this->convertIntoModel($data, $resourceType);
    }

    public function getRepositoryClassForResourceType(ResourceType $resourceType): string
    {
        return Repository::class;
    }

    public function getRepositoryForResourceType(ResourceType $resourceType)
    {
        $repositoryClass = $this->getRepositoryClassForResourceType($resourceType);
        /** @var \Cundd\Rest\VirtualObject\Persistence\RepositoryInterface|\TYPO3\CMS\Extbase\Persistence\RepositoryInterface $repository */
        $repository = $this->objectManager->get($repositoryClass);
        $repository->setConfiguration($this->getConfigurationForResourceType($resourceType));

        return $repository;
    }

    public function getEmptyModelForResourceType(ResourceType $resourceType)
    {
        return new VirtualObject();
    }

    public function getModelData($model)
    {
        $properties = parent::getModelData($model);
        if ($properties === $model) {
            return [];
        }

        return $properties;
    }

    public function getModelProperty($model, string $propertyParameter)
    {
        /** @var VirtualObject $model */
        $modelData = $model->getData();
        $propertyKey = $this->convertPropertyParameterToKey($propertyParameter);
        if (isset($modelData[$propertyKey])) {
            return $modelData[$propertyKey];
        }
        if (isset($modelData[$propertyParameter])) {
            return $modelData[$propertyParameter];
        }

        return null;
    }

    public function saveModel($model, ResourceType $resourceType): void
    {
        /** @var VirtualObject $model */
        /** @var RepositoryInterface $repository */
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($repository) {
            $repository->registerObject($model);
            $this->persistAllChanges();
        }
    }

    public function convertIntoModel(array $data, ResourceType $resourceType)
    {
        try {
            $objectConverter = $this->getObjectConverterForResourceType($resourceType);
            $modelData = $objectConverter->convertFromVirtualObject($this->prepareModelData($data));

            return $objectConverter->convertToVirtualObject($modelData);
        } catch (InvalidPropertyException $exception) {
            $this->logException($exception);

            return null;
        }
    }

    public function persistAllChanges(): void
    {
        // We don't have to do anything because changes are persisted live
    }

    protected function getUidOfModelWithIdentityForResourceType($identifier, ResourceType $resourceType)
    {
        $model = $this->getModelWithIdentityForResourceType($identifier, $resourceType);

        return $model ? $model->getUid() : null;
    }
}
