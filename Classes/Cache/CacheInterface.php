<?php

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
    public function getCachedValueForRequest(RestRequestInterface $request);

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
    );

    /**
     * Return the cache key for the given request
     *
     * @param RestRequestInterface $request
     * @return string
     */
    public function getCacheKeyForRequest(RestRequestInterface $request);

    /**
     * Set the cache lifetime
     *
     * @param int $cacheLifetime
     * @return $this
     */
    public function setCacheLifetime($cacheLifetime);

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
    public function setExpiresHeaderLifetime($expiresHeaderLifetime);

    /**
     * Return the lifetime defined in the expires header
     *
     * @return int
     */
    public function getExpiresHeaderLifetime();
}
