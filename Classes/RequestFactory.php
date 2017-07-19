<?php

namespace Cundd\Rest;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Factory class to get the current Request
 */
class RequestFactory implements SingletonInterface, RequestFactoryInterface
{
    /**
     * @var RestRequestInterface
     */
    private $request;

    /**
     * @var ServerRequestInterface
     */
    private $originalRequest;

    /**
     * @var \Cundd\Rest\Configuration\ConfigurationProviderInterface
     */
    private $configurationProvider;

    /**
     * @var string
     */
    private $factoryClass;

    /**
     * Request Factory constructor
     *
     * @param ConfigurationProviderInterface $configurationProvider
     * @param string                         $factoryClass Class name of the factory for the original request
     */
    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        $factoryClass = '\TYPO3\CMS\Core\Http\ServerRequestFactory'
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->factoryClass = $factoryClass;
    }

    /**
     * Returns the request
     *
     * @return RestRequestInterface
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = $this->buildRequest();
        }

        return $this->request;
    }

    /**
     * Resets the current request
     *
     * @return $this
     */
    public function resetRequest()
    {
        $this->request = null;
        $this->originalRequest = null;

        return $this;
    }

    /**
     * Register/overwrite the current request
     *
     * @param RestRequestInterface $request
     * @return $this
     */
    public function registerCurrentRequest($request)
    {
        $this->resetRequest();
        if ($request instanceof Request) {
            $this->request = $request;
        } else {
            $this->originalRequest = $request;
        }

        return $this;
    }

    /**
     * Check for an alias for the given path
     *
     * @param string $path
     * @return string
     */
    protected function getAliasForPath($path)
    {
        return $this->configurationProvider->getSetting('aliases.' . $path);
    }

    /**
     * Returns the path and original path for the given input path respecting configured aliases
     *
     * @return \stdClass
     */
    protected function determineAndAnalyseInputPath()
    {
        $pathAndFormat = $this->determinePathAndFormat();
        $inputPath = $pathAndFormat->path;

        $pathInfo = (object)[
            'path'         => '',
            'originalPath' => '',
            'resourceType' => '',
            'format'       => $pathAndFormat->format,
        ];

        // Strip the query
        $path = strtok($inputPath, '?');
        if (!$path) {
            return $pathInfo;
        }

        // Get the first part of the path
        $resourceType = strtok($path, '/');
        if (!$resourceType) {
            return $pathInfo;
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
     * @return ServerRequestInterface
     */
    private function getOriginalRequest()
    {
        if ($this->originalRequest) {
            return $this->originalRequest;
        }
        if (!class_exists($this->factoryClass)) {
            throw new \LogicException(sprintf('PSR7 factory class "%s" not found', $this->factoryClass));
        }

        return call_user_func($this->factoryClass . '::fromGlobals');
    }

    /**
     * @param string $path
     * @return string
     */
    private function removePathPrefixes($path)
    {
        $pathPrefix = getenv('TYPO3_REST_REQUEST_BASE_PATH');
        if ($pathPrefix === false) {
            $pathPrefix = $this->configurationProvider->getSetting('absRefPrefix');
        }

        $path = $this->removePathPrefix($path, $pathPrefix);
        $path = $this->removePathPrefix($path, '/rest/');

        return $path;
    }

    /**
     * @param string $path
     * @param string $pathPrefix
     * @return string
     */
    private function removePathPrefix($path, $pathPrefix)
    {
        if ($pathPrefix && $pathPrefix !== 'auto' && $pathPrefix !== '/') {
            $pathPrefix = '/' . trim($pathPrefix, '/');
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
    private function stringHasPrefix($input, $prefix)
    {
        return $input && $prefix && substr($input, 0, strlen($prefix)) === $prefix;
    }

    /**
     * Split path and format
     *
     * @param string $path
     * @return object
     */
    private function splitPathAndFormat($path)
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
     * @return object
     */
    private function determinePathAndFormat()
    {
        $path = $this->getRawPath();

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
     * @return RestRequestInterface
     */
    private function buildRequest()
    {
        $pathInfo = $this->determineAndAnalyseInputPath();
        $originalRequest = $this->getOriginalRequest();
        $internalUri = $originalRequest->getUri()->withPath($pathInfo->path);

        return new Request(
            $originalRequest,
            $internalUri,
            $pathInfo->originalPath,
            new ResourceType($pathInfo->resourceType),
            new Format($pathInfo->format)
        );
    }

    /**
     * Returns if the given format is valid
     *
     * @param $format
     * @return boolean
     */
    public static function isValidFormat($format)
    {
        if (!$format) {
            return false;
        }
        $mimeTypes = Format::MIME_TYPES;

        return isset($mimeTypes[$format]);
    }

    /**
     * @return string
     */
    private function getRawPath()
    {
        $path = '';
        if (isset($_GET['u'])) {
            $path = filter_var($this->removePathPrefixes($_GET['u']), FILTER_SANITIZE_URL);
        }

        if (!$path && isset($_SERVER['REQUEST_URI'])) {
            $path = filter_var($this->removePathPrefixes($_SERVER['REQUEST_URI']), FILTER_SANITIZE_URL);
        }

        return $path;
    }
}
