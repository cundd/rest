<?php
declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\VirtualObject\ConfigurationFactory;
use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\InvalidPropertyException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\Persistence\Repository;
use Cundd\Rest\VirtualObject\Persistence\RepositoryInterface;
use Cundd\Rest\VirtualObject\VirtualObject;
use Psr\Log\LoggerInterface;

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
     * @var ConfigurationFactory
     */
    protected $configurationFactory;

    /**
     * VirtualObjectDataProvider constructor.
     *
     * @param ConfigurationFactory      $configurationFactory
     * @param ObjectManagerInterface    $objectManager
     * @param ExtractorInterface        $extractor
     * @param IdentityProviderInterface $identityProvider
     * @param LoggerInterface|null      $logger
     */
    public function __construct(
        ConfigurationFactory $configurationFactory,
        ObjectManagerInterface $objectManager,
        ExtractorInterface $extractor,
        IdentityProviderInterface $identityProvider,
        LoggerInterface $logger = null
    ) {
        parent::__construct($objectManager, $extractor, $identityProvider, $logger);
        $this->configurationFactory = $configurationFactory;
    }

    /**
     * Return the Object Converter with the matching configuration
     *
     * @param ResourceType|string $resourceType
     * @return ObjectConverter
     */
    public function getObjectConverterForResourceType(ResourceType $resourceType): ObjectConverter
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
     * @return ConfigurationInterface
     * @throws MissingConfigurationException
     */
    public function getConfigurationForResourceType(ResourceType $resourceType): ?ConfigurationInterface
    {
        $resourceTypeString = (string)$resourceType;
        $virtualResourceTypeString = substr(
            $resourceTypeString,
            strpos($resourceTypeString, '-') + 1
        ); // Strip the "VirtualObject-" from the resource type
        if (!$virtualResourceTypeString) {
            throw new MissingConfigurationException('Could not get configuration for empty resource type', 1395932408);
        }

        return $this->configurationFactory->createFromTypoScriptForResourceType(
            new ResourceType($virtualResourceTypeString)
        );
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
        /** @var RepositoryInterface|\TYPO3\CMS\Extbase\Persistence\RepositoryInterface $repository */
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

    public function getModelProperty(object $model, string $propertyParameter)
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

    public function saveModel(object $model, ResourceType $resourceType): void
    {
        /** @var VirtualObject $model */
        /** @var RepositoryInterface $repository */
        $repository = $this->getRepositoryForResourceType($resourceType);
        if ($repository) {
            $repository->registerObject($model);
            $this->persistAllChanges();
        }
    }

    public function convertIntoModel(array $data, ResourceType $resourceType): ?object
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
