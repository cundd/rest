<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\VirtualObject;

class PersistenceManager implements PersistenceManagerInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var BackendInterface
     */
    protected $backend;

    /**
     * @var ObjectConverter
     */
    protected $objectConverter;

    /**
     * The configuration to use when converting
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * Persistence Manager constructor
     *
     * @param ObjectManagerInterface      $objectManager
     * @param BackendInterface            $backend
     * @param ConfigurationInterface|null $configuration
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        BackendInterface $backend,
        ?ConfigurationInterface $configuration = null
    ) {
        $this->objectManager = $objectManager;
        $this->backend = $backend;
        if ($configuration) {
            $this->setConfiguration($configuration);
        }
    }

    public function registerObject(VirtualObject $object): VirtualObject
    {
        $identifierQuery = $this->getIdentifierColumnsOfObject($object);
        if (
            $identifierQuery
            && $this->backend->getObjectCountByQuery($this->getSourceIdentifier(), new Query($identifierQuery))
        ) {
            $this->update($object);
        } else {
            $this->add($object);
        }

        return $object;
    }

    public function add(VirtualObject $object): void
    {
        $identifierValue = $this->backend->addRow(
            $this->getSourceIdentifier(),
            $this->getObjectConverter()->convertFromVirtualObject($object)
        );
        $identifierKey = $this->getConfiguration()->getIdentifier();
        if ($identifierKey) {
            $object->setValueForKey($identifierKey, $identifierValue);
        }
    }

    public function update(VirtualObject $object): void
    {
        $identifierQuery = $this->getIdentifierColumnsOfObject($object);
        $sourceIdentifier = $this->getSourceIdentifier();
        $backend = $this->backend;
        if ($identifierQuery && $backend->getObjectCountByQuery($sourceIdentifier, new Query($identifierQuery))) {
            $backend->updateRow(
                $sourceIdentifier,
                $identifierQuery,
                $this->getObjectConverter()->convertFromVirtualObject($object)
            );
        }
    }

    public function remove(VirtualObject $object): void
    {
        $identifierQuery = $this->getIdentifierColumnsOfObject($object);
        $sourceIdentifier = $this->getSourceIdentifier();
        $backend = $this->backend;
        if ($identifierQuery && $backend->getObjectCountByQuery($sourceIdentifier, new Query($identifierQuery))) {
            $backend->removeRow(
                $sourceIdentifier,
                $identifierQuery
            );
        }
    }

    public function getObjectCountByQuery(QueryInterface $query): int
    {
        return $this->backend->getObjectCountByQuery($this->getSourceIdentifier(), $query);
    }

    public function getObjectDataByQuery(QueryInterface $query): array
    {
        $objectConverter = $this->getObjectConverter();
        $objectCollection = [];

        $rawObjectCollection = $this->backend->getObjectDataByQuery($this->getSourceIdentifier(), $query);
        foreach ($rawObjectCollection as $rawObjectData) {
            $objectCollection[] = $objectConverter->convertToVirtualObject($rawObjectData);
        }

        return $objectCollection;
    }

    public function getObjectByIdentifier($identifier): ?VirtualObject
    {
        $configuration = $this->getConfiguration();

        $identifierProperty = $configuration->getIdentifier();
        $identifierKey = $configuration->getSourceKeyForProperty($identifierProperty);

        $objectConverter = $this->getObjectConverter();
        $query = new Query([$identifierKey => $identifier]);

        $rawObjectCollection = $this->backend->getObjectDataByQuery($this->getSourceIdentifier(), $query);
        foreach ($rawObjectCollection as $rawObjectData) {
            return $objectConverter->convertToVirtualObject($rawObjectData);
        }

        return null;
    }

    public function getIdentifiersOfObject(VirtualObject $object): array
    {
        $objectData = $object->getData();
        $identifier = $this->getConfiguration()->getIdentifier();

        return isset($objectData[$identifier]) ? [$identifier => $objectData[$identifier]] : [];
    }

    public function getIdentifierColumnsOfObject(VirtualObject $object): array
    {
        $configuration = $this->getConfiguration();
        $objectData = $object->getData();
        $identifier = $configuration->getIdentifier();
        $identifierColumn = $configuration->getSourceKeyForProperty($identifier);

        return isset($objectData[$identifier]) ? [$identifierColumn => $objectData[$identifier]] : [];
    }

    public function getSourceIdentifier(): ?string
    {
        return $this->getConfiguration()->getSourceIdentifier();
    }

    public function setConfiguration(ConfigurationInterface $configuration): PersistenceManagerInterface
    {
        $this->configuration = $configuration;
        $this->objectConverter = null;

        return $this;
    }

    public function getConfiguration(): ConfigurationInterface
    {
        if (!$this->configuration) {
            throw new MissingConfigurationException('Configuration not set', 1395681118);
        }

        return $this->configuration;
    }

    /**
     * Returns the Object Converter for the current configuration
     *
     * @return ObjectConverter
     */
    protected function getObjectConverter(): ObjectConverter
    {
        if (!$this->objectConverter) {
            $this->objectConverter = new ObjectConverter($this->getConfiguration());
        }

        return $this->objectConverter;
    }
}
