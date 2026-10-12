<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Request\ResourceType;

final class ResourceConfiguration
{
    /**
     * @param non-empty-string[]                         $aliases
     * @param class-string<HandlerInterface>|string      $handlerClass
     * @param class-string<DataProviderInterface>|string $dataProviderClass
     */
    public function __construct(
        public readonly ResourceType $resourceType,
        public readonly AccessRequirement $readAccess,
        public readonly AccessRequirement $writeAccess,
        public readonly int $cacheLifetime,
        public readonly string $handlerClass,
        public readonly string $dataProviderClass,
        public readonly array $aliases,
        public readonly int $expiresHeaderLifetime = -1,
    ) {
        $this->assertStringArray($aliases);
    }

    /**
     * @phpstan-assert array<string> $aliases
     *
     * @param array<int,mixed> $aliases
     */
    private function assertStringArray(array $aliases): void
    {
        foreach ($aliases as $alias) {
            if (!is_string($alias)) {
                throw new InvalidArgumentException('Only strings are allowed as aliases');
            }
        }
    }
}
