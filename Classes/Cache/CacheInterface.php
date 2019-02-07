<?php
declare(strict_types=1);

namespace Cundd\Rest\Cache;

use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * The class caches responses of requests
 */
interface CacheInterface
{
    /**
     * Return the cached value for the given request or NULL if it is not defined
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface|null
     */
    public function getCachedValueForRequest(RestRequestInterface $request): ?ResponseInterface;

    /**
     * Set the cache value for the given request
     *
     * @param RestRequestInterface  $request
     * @param ResponseInterface     $response
     * @param ResourceConfiguration $resourceConfiguration
     * @return void
     */
    public function setCachedValueForRequest(
        RestRequestInterface $request,
        ResponseInterface $response,
        ResourceConfiguration $resourceConfiguration
    ): void;

    /**
     * Return the cache key for the given request
     *
     * @param RestRequestInterface $request
     * @return string
     */
    public function getCacheKeyForRequest(RestRequestInterface $request): string;

    /**
     * Set the cache lifetime
     *
     * @param int $cacheLifetime
     * @return $this
     */
    public function setCacheLifetime(int $cacheLifetime): self;

    /**
     * Return the cache lifetime
     *
     * @return int
     */
    public function getCacheLifetime();

    /**
     * Set the lifetime defined in the expires header
     *
     * @param int $expiresHeaderLifetime
     * @return $this
     */
    public function setExpiresHeaderLifetime(int $expiresHeaderLifetime): self;

    /**
     * Return the lifetime defined in the expires header
     *
     * @return int
     */
    public function getExpiresHeaderLifetime();
}
