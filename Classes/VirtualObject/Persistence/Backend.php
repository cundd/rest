<?php
declare(strict_types=1);

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
     * @param BackendInterface|null $concreteBackend
     */
    public function __construct(BackendInterface $concreteBackend = null)
    {
        $this->concreteBackend = $concreteBackend ? $concreteBackend : BackendFactory::getBackend();
    }

    public function addRow(string $tableName, array $row): int
    {
        return $this->concreteBackend->addRow($tableName, $row);
    }

    public function updateRow(string $tableName, array $identifier, array $row): int
    {
        return $this->concreteBackend->updateRow($tableName, $identifier, $row);
    }

    public function removeRow(string $tableName, array $identifier): int
    {
        return $this->concreteBackend->removeRow($tableName, $identifier);
    }

    public function getObjectCountByQuery(string $tableName, QueryInterface $query): int
    {
        return $this->concreteBackend->getObjectCountByQuery($tableName, $query);
    }

    public function getObjectDataByQuery(string $tableName, QueryInterface $query): array
    {
        return $this->concreteBackend->getObjectDataByQuery($tableName, $query);
    }

    public function executeQuery(string $query)
    {
        return $this->concreteBackend->executeQuery($query);
    }
}
