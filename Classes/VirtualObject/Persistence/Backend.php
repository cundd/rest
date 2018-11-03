<?php

namespace Cundd\Rest\VirtualObject\Persistence;

class Backend implements BackendInterface, RawQueryBackendInterface
{
    /**
     * @var BackendInterface
     */
    private $concreteBackend;

    /**
     * Backend constructor.
     *
     * @param BackendInterface $concreteBackend
     */
    public function __construct(BackendInterface $concreteBackend = null)
    {
        $this->concreteBackend = $concreteBackend ? $concreteBackend : BackendFactory::getBackend();
    }

    public function addRow($tableName, array $row)
    {
        return $this->concreteBackend->addRow($tableName, $row);
    }

    public function updateRow($tableName, array $identifier, array $row)
    {
        return $this->concreteBackend->updateRow($tableName, $identifier, $row);
    }

    public function removeRow($tableName, array $identifier)
    {
        return $this->concreteBackend->removeRow($tableName, $identifier);
    }

    public function getObjectCountByQuery($tableName, $query)
    {
        return $this->concreteBackend->getObjectCountByQuery($tableName, $query);
    }

    public function getObjectDataByQuery($tableName, $query)
    {
        return $this->concreteBackend->getObjectDataByQuery($tableName, $query);
    }

    public function executeQuery($query)
    {
        return $this->concreteBackend->executeQuery($query);
    }
}
