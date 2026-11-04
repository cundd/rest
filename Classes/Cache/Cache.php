<?php

declare(strict_types=1);

namespace Cundd\Rest\Cache;

use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The class caches responses of requests
 */
class Cache implements CacheInterface
{
    private const CACHE_ENTRY_KEY_CONTENT = 'content';
    private const CACHE_ENTRY_KEY_STATUS = 'status';
    private const CACHE_ENTRY_KEY_HEADERS = 'headers';

    /**
     * Concrete cache instance
     */
    private FrontendInterface $cacheInstance;

    /**
     * Cache lifetime
     */
    private int $cacheLifetime;

    /**
     * Lifetime sent in the expires header
     */
    private int $expiresHeaderLifetime;

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function getCachedValueForRequest(
        RestRequestInterface $request,
    ): ?ResponseInterface {
        $cacheLifetime = $this->getCacheLifetime();

        /*
         * Use caching if the cache lifetime configuration is not -1, an API
         * path is given and the request is a read request
         */
        $useCaching = (-1 !== $cacheLifetime) && $request->getPath();
        if (!$useCaching) {
            return null;
        }

        if ($request->isWrite()) {
            $this->clearCache($request);

            return null;
        }

        if (!$request->isRead()) {
            return null;
        }

        $key = $this->getCacheKeyForRequest($request);

        $cacheInstance = $this->getCacheInstance();
        if (!$cacheInstance->has($key)) {
            return null;
        }
        $responseData = $cacheInstance->get($key);
        if (!is_array($responseData)) {
            return null;
        }

        /** TODO: Send 304 status if appropriate */
        $response = $this->responseFactory->createResponse(
            $responseData[self::CACHE_ENTRY_KEY_CONTENT],
            intval($responseData[self::CACHE_ENTRY_KEY_STATUS])
        );

        // Restore all cached headers
        $headers = $responseData[self::CACHE_ENTRY_KEY_HEADERS];
        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response
            ->withHeader(
                Header::EXPIRES,
                $this->getHttpDate(time() + $this->getExpiresHeaderLifetime())
            )
            ->withHeader(
                Header::CUNDD_REST_CACHED,
                'true'
            );
    }

    public function setCachedValueForRequest(
        RestRequestInterface $request,
        ResponseInterface $response,
        ResourceConfiguration $resourceConfiguration,
    ): void {
        if (false === $this->canBeCached($request, $response)) {
            return;
        }

        $cacheLifetime = $this->getCacheLifetime();

        /*
         * Use caching if the cache lifetime configuration is not -1, an API
         * path is given and the request is a read request
         */
        $useCaching = (-1 !== $cacheLifetime) && $request->getPath();
        if (!$useCaching) {
            return;
        }

        /** @var VariableFrontend $cacheInstance */
        $cacheInstance = $this->getCacheInstance();

        $headers = array_merge(
            $response->getHeaders(),
            [Header::LAST_MODIFIED => $this->getHttpDate(time())]
        );
        $cacheInstance->set(
            $this->getCacheKeyForRequest($request),
            [
                self::CACHE_ENTRY_KEY_CONTENT => (string) $response->getBody(),
                self::CACHE_ENTRY_KEY_STATUS  => $response->getStatusCode(),
                self::CACHE_ENTRY_KEY_HEADERS => $headers,
            ],
            $this->getTags($request),
            $cacheLifetime
        );
    }

    public function getCacheKeyForRequest(RestRequestInterface $request): string
    {
        $cacheKey = sha1(
            $request->getUri()
                . '_' . $request->getFormat()
                . '_' . $request->getMethod()
        );
        $params = $request->getQueryParams();
        if ('GET' === $request->getMethod() && !empty($params)) {
            $cacheKey = sha1($cacheKey . serialize($params));
        }

        return $cacheKey;
    }

    public function setCacheLifetime(int $cacheLifetime): CacheInterface
    {
        $this->cacheLifetime = $cacheLifetime;

        return $this;
    }

    public function getCacheLifetime(): int
    {
        return $this->cacheLifetime;
    }

    public function setExpiresHeaderLifetime(
        int $expiresHeaderLifetime,
    ): CacheInterface {
        $this->expiresHeaderLifetime = $expiresHeaderLifetime;

        return $this;
    }

    public function getExpiresHeaderLifetime(): int
    {
        return $this->expiresHeaderLifetime;
    }

    /**
     * Set the concrete Cache instance
     *
     * @internal
     */
    public function setCacheInstance(FrontendInterface $cacheInstance): void
    {
        $this->cacheInstance = $cacheInstance;
    }

    /**
     * Return a date in the format for a HTTP header
     */
    private function getHttpDate(int $date): string
    {
        return gmdate('D, d M Y H:i:s \G\M\T', $date);
    }

    /**
     * Return the cache instance
     */
    private function getCacheInstance(): FrontendInterface
    {
        if (!isset($this->cacheInstance)) {
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $this->cacheInstance = $cacheManager->getCache('cundd_rest_cache');
        }

        return $this->cacheInstance;
    }

    /**
     * Clear the cache for the current request
     */
    private function clearCache(RestRequestInterface $request): void
    {
        $allTags = $this->getTags($request);
        $this->getCacheInstance()->flushByTags($allTags);
    }

    /**
     * Return the tags for the current request
     *
     * @return string[]
     */
    private function getTags(RestRequestInterface $request): array
    {
        $currentPath = $request->getPath();
        $resourceType = $request->getResourceType();
        [$vendor, $extension, $model] = Utility::getClassNamePartsForResourceType($resourceType);

        return array_filter(
            array_map(
                fn ($tag) => preg_replace('/[^a-zA-Z0-9_%\\-&]/', '', $tag),
                [
                    $vendor . '_' . $extension . '_' . $model,
                    $extension . '_' . $model,
                    $currentPath,
                    (string) $resourceType,
                ]
            )
        );
    }

    /**
     * Return if the given Request-Response combination can be cached
     *
     * @internal
     */
    public function canBeCached(
        RestRequestInterface $request,
        ResponseInterface $response,
    ): bool {
        // Don't cache write requests
        if ($request->isWrite()) {
            return false;
        }

        if ($this->cacheControlPreventsCaching($response)) {
            return false;
        }

        if ($response->getStatusCode() >= 500) {
            return false;
        }

        return !$response->getHeader(Header::CUNDD_REST_NO_CACHE);
    }

    private function cacheControlPreventsCaching(
        ResponseInterface $response,
    ): bool {
        $cacheControlHeaders = $response->getHeader(Header::CACHE_CONTROL);
        $noCacheValues = [
            'private',
            'no-cache',
            'no-store',
            'must-revalidate',
        ];
        foreach ($cacheControlHeaders as $cacheControlHeader) {
            if (0 < count(array_intersect(explode(',', (string) $cacheControlHeader), $noCacheValues))) {
                return true;
            }
        }

        return false;
    }
}
