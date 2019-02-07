<?php

namespace Cundd\Rest\Cache;

use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The class caches responses of requests
 */
class Cache implements CacheInterface
{
    /**
     * Concrete cache instance
     *
     * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
     */
    private $cacheInstance;

    /**
     * Cache life time
     *
     * @var integer
     */
    private $cacheLifetime;

    /**
     * Life time defined in the expires header
     *
     * @var integer
     */
    private $expiresHeaderLifetime;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * Cache constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function getCachedValueForRequest(RestRequestInterface $request)
    {
        $cacheLifetime = $this->getCacheLifetime();

        /*
         * Use caching if the cache life time configuration is not -1, an API
         * path is given and the request is a read request
         */
        $useCaching = ($cacheLifetime !== -1) && $request->getPath();
        if (!$useCaching) {
            return null;
        }

        $cacheInstance = $this->getCacheInstance();
        $responseData = $cacheInstance->get($this->getCacheKeyForRequest($request));
        if (!$responseData) {
            return null;
        }

        if (!$request->isRead()) {
            $this->clearCache($request);

            return null;
        }

        /** TODO: Send 304 status if appropriate */
        $response = $this->responseFactory->createResponse($responseData['content'], intval($responseData['status']));

        return $response
            ->withHeader(Header::CONTENT_TYPE, $responseData[Header::CONTENT_TYPE])
            ->withHeader(Header::LAST_MODIFIED, $responseData[Header::LAST_MODIFIED])
            ->withHeader(Header::EXPIRES, $this->getHttpDate(time() + $this->getExpiresHeaderLifetime()))
            ->withHeader(Header::CUNDD_REST_CACHED, 'true');
    }

    public function setCachedValueForRequest(
        RestRequestInterface $request,
        ResponseInterface $response,
        ResourceConfiguration $resourceConfiguration
    ) {
        if (false === $this->canBeCached($request, $response)) {
            return;
        }

        $cacheLifetime = $this->getCacheLifetime();

        /*
         * Use caching if the cache life time configuration is not -1, an API
         * path is given and the request is a read request
         */
        $useCaching = ($cacheLifetime !== -1) && $request->getPath();
        if (!$useCaching) {
            return;
        }

        /** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend $cacheInstance */
        $cacheInstance = $this->getCacheInstance();
        $cacheInstance->set(
            $this->getCacheKeyForRequest($request),
            [
                'content'             => (string)$response->getBody(),
                'status'              => $response->getStatusCode(),
                Header::CONTENT_TYPE  => $response->getHeader(Header::CONTENT_TYPE),
                Header::LAST_MODIFIED => $this->getHttpDate(time()),
            ],
            $this->getTags($request),
            $cacheLifetime
        );
    }

    /**
     * Returns the cache key for the given request
     *
     * @param RestRequestInterface $request
     * @return string
     */
    public function getCacheKeyForRequest(RestRequestInterface $request)
    {
        $cacheKey = sha1($request->getUri() . '_' . $request->getFormat() . '_' . $request->getMethod());
        $params = $request->getQueryParams();
        if ($request->getMethod() === 'GET' && count($params)) {
            $cacheKey = sha1($cacheKey . serialize($params));
        }

        return $cacheKey;
    }

    /**
     * Sets the cache life time
     *
     * @param int $cacheLifetime
     * @return $this
     */
    public function setCacheLifetime($cacheLifetime)
    {
        $this->cacheLifetime = $cacheLifetime;

        return $this;
    }

    /**
     * Returns the cache life time
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return $this->cacheLifetime;
    }

    /**
     * Sets the life time defined in the expires header
     *
     * @param int $expiresHeaderLifetime
     * @return $this
     */
    public function setExpiresHeaderLifetime($expiresHeaderLifetime)
    {
        $this->expiresHeaderLifetime = $expiresHeaderLifetime;

        return $this;
    }

    /**
     * Returns the life time defined in the expires header
     *
     * @return int
     */
    public function getExpiresHeaderLifetime()
    {
        return $this->expiresHeaderLifetime;
    }

    /**
     * Sets the concrete Cache instance
     *
     * @param \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend $cacheInstance
     * @internal
     */
    public function setCacheInstance($cacheInstance)
    {
        $this->cacheInstance = $cacheInstance;
    }

    /**
     * Returns a date in the format for a HTTP header
     *
     * @param int $date
     * @return string
     */
    private function getHttpDate($date)
    {
        return gmdate('D, d M Y H:i:s \G\M\T', $date);
    }

    /**
     * Returns the cache instance
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface|\TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
     */
    private function getCacheInstance()
    {
        if (!$this->cacheInstance) {
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $this->cacheInstance = $cacheManager->getCache('cundd_rest_cache');
        }

        return $this->cacheInstance;
    }

    /**
     * Clears the cache for the current request
     *
     * @param RestRequestInterface $request
     */
    private function clearCache(RestRequestInterface $request)
    {
        $allTags = $this->getTags($request);
        $firstTag = $allTags[0];
        $this->getCacheInstance()->flushByTag($firstTag);
    }

    /**
     * Returns the tags for the current request
     *
     * @param RestRequestInterface $request
     * @return array [string]
     */
    private function getTags(RestRequestInterface $request)
    {
        $currentPath = $request->getPath();
        list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType($request->getResourceType());

        return array_filter(
            array_map(
                function ($tag) {
                    return preg_replace('/[^a-zA-Z0-9_%\\-&]/', '', $tag);
                },
                [
                    $vendor . '_' . $extension . '_' . $model,
                    $extension . '_' . $model,
                    $currentPath,
                ]
            )
        );
    }

    /**
     * Return if the given Request-Response combination can be cached
     *
     * @param RestRequestInterface $request
     * @param ResponseInterface    $response
     * @return bool
     */
    public function canBeCached(RestRequestInterface $request, ResponseInterface $response)
    {
        // Don't cache write requests
        if ($request->isWrite()) {
            return false;
        }

        if ($this->cacheControlPreventsCaching($response)) {
            return false;
        }

        if ($response->getHeader(Header::CUNDD_REST_NO_CACHE)) {
            return false;
        }

        return true;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function cacheControlPreventsCaching(ResponseInterface $response)
    {
        $cacheControlHeaders = $response->getHeader(Header::CACHE_CONTROL);
        $noCacheValues = [
            'private',
            'no-cache',
            'no-store',
            'must-revalidate',
        ];
        foreach ($cacheControlHeaders as $cacheControlHeader) {
            if (0 < count(array_intersect(explode(',', (string)$cacheControlHeader), $noCacheValues))) {
                return true;
            }
        }

        return false;
    }
}
