<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\Format;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\Utility\SiteLanguageUtility;
use Cundd\Rest\Utility\SiteUtility;
use Psr\Http\Message\ServerRequestInterface;

use function basename;
use function dirname;
use function filter_var;
use function getenv;
use function is_numeric;
use function ltrim;
use function preg_replace;
use function rtrim;
use function strlen;
use function strrpos;
use function strtok;
use function substr;
use function trim;

/**
 * Factory class to get the current Request
 *
 * @phpstan-type RequestPathInfo object{
 *     path:string,
 *     originalPath:string,
 *     resourceType:string,
 *     format:string
 * }
 */
final readonly class RequestFactory implements SingletonInterface, RequestFactoryInterface
{
    public function __construct(
        private ConfigurationProviderFactoryInterface $configurationProviderFactory,
    ) {
    }

    public function buildRequest(ServerRequestInterface $request): RestRequestInterface
    {
        $pathInfo = $this->determineAndAnalyseInputPath($request);
        $originalRequest = $request;
        $internalUri = $request->getUri()->withPath($pathInfo->path);

        return new Request(
            $originalRequest,
            $internalUri,
            $pathInfo->originalPath,
            new ResourceType($pathInfo->resourceType),
            new Format($pathInfo->format)
        );
    }

    /**
     * Check for an alias for the given path
     */
    private function getAliasForPath(
        ServerRequestInterface $request,
        string $path,
    ): ?string {
        return $this->configurationProviderFactory
            ->build($request)
            ->getSetting('aliases.' . $path);
    }

    /**
     * Return the path and original path for the given input path respecting configured aliases
     *
     * @return RequestPathInfo
     */
    private function determineAndAnalyseInputPath(
        ServerRequestInterface $request,
    ): object {
        $pathAndFormat = $this->determinePathAndFormat($request);
        $inputPath = $pathAndFormat->path;

        $pathInfo = (object) [
            'path'         => '',
            'originalPath' => '',
            'resourceType' => '',
            'format'       => $pathAndFormat->format,
        ];

        if (!$inputPath) {
            return $pathInfo;
        }

        // Strip the query
        $path = strtok($inputPath, '?');
        if (!$path) {
            return $pathInfo;
        }

        // Get the first part of the path
        $resourceType = strtok($path, '/');
        if (!$resourceType) {
            return (object) [
                'path'         => $path,
                'originalPath' => '',
                'resourceType' => '',
                'format'       => $pathAndFormat->format,
            ];
        }

        // Check for path aliases
        $resourceTypeAlias = $this->getAliasForPath($request, $resourceType);
        if ($resourceTypeAlias) {
            $patchedPath = (string) preg_replace(
                '!' . preg_quote($resourceType, '!') . '!',
                $resourceTypeAlias,
                $path,
                1
            );

            return (object) [
                'path'         => $patchedPath,
                'originalPath' => $path,
                'resourceType' => $resourceTypeAlias,
                'format'       => $pathAndFormat->format,
            ];
        }

        return (object) [
            'path'         => $path,
            'originalPath' => $path,
            'resourceType' => $resourceType,
            'format'       => $pathAndFormat->format,
        ];
    }

    private function removePathPrefixes(
        ServerRequestInterface $request,
        string $path,
    ): string {
        $pathPrefix = getenv('TYPO3_REST_REQUEST_BASE_PATH') ?: getenv('REDIRECT_TYPO3_REST_REQUEST_BASE_PATH');
        if (false === $pathPrefix) {
            $pathPrefix = $this->configurationProviderFactory
                ->build($request)
                ->getSetting('TYPO3_REST_REQUEST_BASE_PATH', false);
        }
        if (false === $pathPrefix) {
            $pathPrefix = $this->configurationProviderFactory
                ->build($request)
                ->getSetting('absRefPrefix', '');
        }

        $path = $this->removePathPrefix($path, '/' . trim((string) $pathPrefix, '/'));
        $path = $this->removePathPrefix($path, '/rest/');

        $sitePrefix = SiteUtility::detectSitePrefix($request);
        $path = $this->removePathPrefix($path, rtrim($sitePrefix, '/') . '/rest/');

        // The Site-Language Prefix also contains the Site Prefix
        $siteLanguagePrefix = SiteLanguageUtility::detectSiteLanguagePrefix($request);
        $path = $this->removePathPrefix($path, rtrim($siteLanguagePrefix, '/') . '/rest/');

        return $path;
    }

    private function removePathPrefix(string $path, string $pathPrefix): string
    {
        if ($pathPrefix && 'auto' !== $pathPrefix && '/' !== $pathPrefix) {
            if ($this->stringHasPrefix($path, $pathPrefix)) {
                $path = substr($path, strlen($pathPrefix));
            }
        }

        return $path;
    }

    private function stringHasPrefix(string $input, string $prefix): bool
    {
        return $input && $prefix && substr($input, 0, strlen($prefix)) === $prefix;
    }

    /**
     * Split path and format
     *
     * @return object{path:string,format:string}
     */
    private function splitPathAndFormat(string $path): object
    {
        $format = '';

        // Strip the format from the path
        $resourceName = basename($path);
        $lastDotPosition = strrpos($resourceName, '.');
        if (false !== $lastDotPosition) {
            $directory = '';
            if ($resourceName !== $path) {
                $directory = rtrim(dirname($path), '/') . '/';
            }
            $path = $directory . substr($resourceName, 0, $lastDotPosition);
            $format = substr($resourceName, $lastDotPosition + 1);
        }

        $path = '/' . ltrim($path, '/');

        // If the format is numeric it must not be a format
        if (is_numeric($format)) {
            $path = $path . '.' . $format;
            $format = '';
        }
        if (!$this->isValidFormat($format)) {
            $format = Format::DEFAULT_FORMAT;
        }

        return (object) [
            'path'   => $path,
            'format' => $format,
        ];
    }

    /**
     * @return object{path:string,format:string}
     */
    private function determinePathAndFormat(ServerRequestInterface $request): object
    {
        $path = $this->getRawPath($request);

        // Make sure the path starts with a slash
        if ($path) {
            $path = '/' . ltrim((string) $path, '/');
        }

        // Strip the query
        $path = strtok($path, '?');
        if (!$path) {
            return (object) [
                'path'   => '',
                'format' => Format::DEFAULT_FORMAT,
            ];
        }

        // Extract path and format
        return $this->splitPathAndFormat($path);
    }

    /**
     * Return if the given format is valid
     */
    public static function isValidFormat(mixed $format): bool
    {
        if (!is_string($format)) {
            return false;
        }

        if (!$format) {
            return false;
        }

        $mimeTypes = Format::MIME_TYPES;

        return isset($mimeTypes[$format]);
    }

    private function getRawPath(ServerRequestInterface $request): string
    {
        return (string) filter_var(
            $this->removePathPrefixes(
                $request,
                $request->getUri()->getPath()
            ),
            FILTER_SANITIZE_URL
        );
    }
}
