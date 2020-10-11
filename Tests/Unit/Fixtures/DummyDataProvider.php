<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Fixtures;

use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use RuntimeException;

class DummyDataProvider implements DataProviderInterface
{
    public function fetchAllModels(ResourceType $resourceType):iterable
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function countAllModels(ResourceType $resourceType): int
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function fetchModel($identifier, ResourceType $resourceType):object
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function createModel(array $data, ResourceType $resourceType)
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function convertIntoModel(array $data, ResourceType $resourceType):?object
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function getModelData($model)
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function getModelProperty($model, string $propertyParameter)
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function saveModel($model, ResourceType $resourceType): void
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function removeModel($model, ResourceType $resourceType): void
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

}
