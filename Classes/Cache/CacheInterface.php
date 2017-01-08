<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 30.12.16
 * Time: 11:46
 */
namespace Cundd\Rest\Cache;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * The class caches responses of requests
 */
interface CacheInterface
{
    /**
     * Returns the cached value for the given request or NULL if it is not defined
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface|null
     */
    public function getCachedValueForRequest(RestRequestInterface $request);

    /**
     * Sets the cache value for the given request
     *
     * @param RestRequestInterface $request
     * @param ResponseInterface    $response
     */
    public function setCachedValueForRequest(RestRequestInterface $request, ResponseInterface $response);

    /**
     * Returns the cache key for the given request
     *
     * @param RestRequestInterface $request
     * @return string
     */
    public function getCacheKeyForRequest(RestRequestInterface $request);

    /**
     * Sets the cache life time
     *
     * @param int $cacheLifeTime
     * @return $this
     */
    public function setCacheLifeTime($cacheLifeTime);

    /**
     * Returns the cache life time
     *
     * @return int
     */
    public function getCacheLifeTime();

    /**
     * Sets the life time defined in the expires header
     *
     * @param int $expiresHeaderLifeTime
     * @return $this
     */
    public function setExpiresHeaderLifeTime($expiresHeaderLifeTime);

    /**
     * Returns the life time defined in the expires header
     *
     * @return int
     */
    public function getExpiresHeaderLifeTime();
}
