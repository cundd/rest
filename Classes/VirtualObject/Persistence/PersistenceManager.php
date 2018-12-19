<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\VirtualObject;

class PersistenceManager implements PersistenceManagerInterface
{
    /**
     * @var \Cundd\Rest\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @var \Cundd\Rest\VirtualObject\Persistence\BackendInterface
     * @inject
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
     * Registers the given Virtual Object
     *
     * This is a high level shorthand for:
     * Object exists?
     *    Yes -> update
     *    No -> add
     *
     * @param VirtualObject $object
     * @return VirtualObject Returns the registered Document
     */
    public function registerObject($object)
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

    public function add($object)
    {
        $identifierValue = $this->backend->addRow(
            $this->getSourceIdentifier(),
            $this->getObjectConverter()->convertFromVirtualObject($object)
        );
        $identifierKey = $this->getConfiguration()->getIdentifier();
        $object->setValueForKey($identifierKey, $identifierValue);
    }

    public function update($object)
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

    public function remove($object)
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

    public function getObjectDataByQuery(QueryInterface $query)
    {
        $objectConverter = $this->getObjectConverter();
        $objectCollection = [];

        $rawObjectCollection = $this->backend->getObjectDataByQuery($this->getSourceIdentifier(), $query);
        foreach ($rawObjectCollection as $rawObjectData) {
            $objectCollection[] = $objectConverter->convertToVirtualObject($rawObjectData);
        }

        return $objectCollection;
    }

    public function getObjectByIdentifier($identifier)
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

    public function getIdentifiersOfObject($object)
    {
        $objectData = $object->getData();
        $identifier = $this->getConfiguration()->getIdentifier();

        return isset($objectData[$identifier]) ? [$identifier => $objectData[$identifier]] : [];
    }

    public function getIdentifierColumnsOfObject($object)
    {
        $configuration = $this->getConfiguration();
        $objectData = $object->getData();
        $identifier = $configuration->getIdentifier();
        $identifierColumn = $configuration->getSourceKeyForProperty($identifier);

        return isset($objectData[$identifier]) ? [$identifierColumn => $objectData[$identifier]] : [];
    }

    public function getSourceIdentifier()
    {
        return $this->getConfiguration()->getSourceIdentifier();
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        $this->objectConverter = null;

        return $this;
    }

    public function getConfiguration()
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
    protected function getObjectConverter()
    {
        if (!$this->objectConverter) {
            $this->objectConverter = new ObjectConverter($this->getConfiguration());
        }

        return $this->objectConverter;
    }
}
