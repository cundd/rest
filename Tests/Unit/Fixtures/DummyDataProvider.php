<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Fixtures;

use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Http\RestRequestInterface;
use RuntimeException;

class DummyDataProvider implements DataProviderInterface
{
    public function fetchAllModels(RestRequestInterface $request): iterable
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function countAllModels(RestRequestInterface $request): int
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function fetchModel(RestRequestInterface $request, int|array|string $identifier): object
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function createModel(RestRequestInterface $request, array $data): ?object
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function convertIntoModel(RestRequestInterface $request, array $data): ?object
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function getModelData(RestRequestInterface $request, mixed $model): mixed
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function getModelProperty(
        RestRequestInterface $request,
        $model,
        string $propertyParameter
    ): mixed
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function saveModel(RestRequestInterface $request, $model): void
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }

    public function removeModel(RestRequestInterface $request, $model): void
    {
        throw new RuntimeException(__METHOD__ . ' not implemented');
    }
}
