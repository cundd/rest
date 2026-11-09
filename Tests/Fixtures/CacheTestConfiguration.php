<?php

namespace Cundd\Rest\Tests\Fixtures;

final readonly class CacheTestConfiguration
{
    public function __construct(
        public ?int $resourceTypeExpiresHeaderLifetime,
        public int $resourceTypeCacheLifetime,
        public ?int $globalCacheLifetime,
        public ?int $globalExpiresHeaderLifetime,
        public int $expectedCacheLifetime,
        public int $expectedExpiresHeaderLifetime,
    ) {
    }
}
