<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Utility\SiteLanguageUtility;
use Cundd\Rest\Utility\SiteUtility;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

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
 */
class RequestFactory implements SingletonInterface, RequestFactoryInterface
{
    /**
     * @var ConfigurationProviderInterface
     */
    private ConfigurationProviderInterface $configurationProvider;

    public function __construct(ConfigurationProviderInterface $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
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
     *
     * @param string $path
     * @return string|null
     */
    protected function getAliasForPath(string $path): ?string
    {
        return $this->configurationProvider->getSetting('aliases.' . $path);
    }

    /**
     * Returns the path and original path for the given input path respecting configured aliases
     *
     * @param ServerRequestInterface $request
     * @return stdClass
     */
    protected function determineAndAnalyseInputPath(ServerRequestInterface $request): stdClass
    {
        $pathAndFormat = $this->determinePathAndFormat($request);
        $inputPath = $pathAndFormat->path;

        $pathInfo = (object)[
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
            return (object)[
                'path'         => $path,
                'originalPath' => '',
                'resourceType' => '',
                'format'       => $pathAndFormat->format,
            ];
        }

        // Check for path aliases
        $resourceTypeAlias = $this->getAliasForPath($resourceType);
        if ($resourceTypeAlias) {
            return (object)[
                'path'         => preg_replace('!' . $resourceType . '!', $resourceTypeAlias, $path, 1),
                'originalPath' => $path,
                'resourceType' => $resourceTypeAlias,
                'format'       => $pathAndFormat->format,
            ];
        }

        return (object)[
            'path'         => $path,
            'originalPath' => $path,
            'resourceType' => $resourceType,
            'format'       => $pathAndFormat->format,
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $path
     * @return string
     */
    private function removePathPrefixes(ServerRequestInterface $request, string $path): string
    {
        $pathPrefix = getenv('TYPO3_REST_REQUEST_BASE_PATH') ?: getenv('REDIRECT_TYPO3_REST_REQUEST_BASE_PATH');
        if ($pathPrefix === false) {
            $pathPrefix = $this->configurationProvider->getSetting('TYPO3_REST_REQUEST_BASE_PATH', false);
        }
        if ($pathPrefix === false) {
            $pathPrefix = $this->configurationProvider->getSetting('absRefPrefix');
        }

        $path = $this->removePathPrefix($path, '/' . trim((string)$pathPrefix, '/'));
        $path = $this->removePathPrefix($path, '/rest/');

        $sitePrefix = SiteUtility::detectSitePrefix($request);
        $path = $this->removePathPrefix($path, rtrim($sitePrefix, '/') . '/rest/');
        // The Site-Language Prefix also contains the Site Prefix
        $siteLanguagePrefix = SiteLanguageUtility::detectSiteLanguagePrefix($request);
        $path = $this->removePathPrefix($path, $siteLanguagePrefix . 'rest/');

        return $path;
    }

    /**
     * @param string $path
     * @param string $pathPrefix
     * @return string
     */
    private function removePathPrefix(string $path, string $pathPrefix): string
    {
        if ($pathPrefix && $pathPrefix !== 'auto' && $pathPrefix !== '/') {
            if ($this->stringHasPrefix($path, $pathPrefix)) {
                $path = substr($path, strlen($pathPrefix));
            }
        }

        return $path;
    }

    /**
     * @param string $input
     * @param string $prefix
     * @return bool
     */
    private function stringHasPrefix(string $input, string $prefix): bool
    {
        return $input && $prefix && substr($input, 0, strlen($prefix)) === $prefix;
    }

    /**
     * Split path and format
     *
     * @param string $path
     * @return object
     */
    private function splitPathAndFormat(string $path): object
    {
        $format = '';

        // Strip the format from the path
        $resourceName = basename($path);
        $lastDotPosition = strrpos($resourceName, '.');
        if ($lastDotPosition !== false) {
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
        if (!$format || !$this->isValidFormat($format)) {
            $format = Format::DEFAULT_FORMAT;
        }

        return (object)[
            'path'   => $path,
            'format' => $format,
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @return object
     */
    private function determinePathAndFormat(ServerRequestInterface $request): object
    {
        $path = $this->getRawPath($request);

        // Make sure the path starts with a slash
        if ($path) {
            $path = '/' . ltrim((string)$path, '/');
        }

        // Strip the query
        $path = strtok($path, '?');
        if (!$path) {
            return (object)[
                'path'   => '',
                'format' => Format::DEFAULT_FORMAT,
            ];
        }

        // Extract path and format
        return $this->splitPathAndFormat($path);
    }

    /**
     * Returns if the given format is valid
     *
     * @param $format
     * @return boolean
     */
    public static function isValidFormat($format): bool
    {
        if (!$format) {
            return false;
        }
        $mimeTypes = Format::MIME_TYPES;

        return isset($mimeTypes[$format]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getRawPath(ServerRequestInterface $request): string
    {
        $path = '';
        if (isset($_GET['u'])) {
            $path = filter_var($this->removePathPrefixes($request, $_GET['u']), FILTER_SANITIZE_URL);
        }

        if (!$path) {
            $path = filter_var($this->removePathPrefixes($request, $request->getUri()->getPath()), FILTER_SANITIZE_URL);
        }

        return (string)$path;
    }
}
