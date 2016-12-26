<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 01.08.15
 * Time: 21:18
 */

namespace Cundd\Rest;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Factory class to get the current Request
 *
 * @package Cundd\Rest
 */
class RequestFactory implements SingletonInterface, RequestFactoryInterface
{
    /**
     * API path
     *
     * @var string
     */
    protected $uri;

    /**
     * The response format
     *
     * @var string
     */
    protected $format;

    /**
     * @var \Cundd\Rest\Request
     */
    protected $request;

    /**
     * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     * @inject
     */
    protected $configurationProvider;

    /**
     * Returns the request
     *
     * @return \Cundd\Rest\Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            $uri = $this->getUri();

            /*
             * Transform Document URLs
             * @Todo: Make this more flexible
             */
            if ($this->stringHasPrefix($uri, Request::API_PATH_DOCUMENT . '/')) {
                $documentApiPathLength = strlen(Request::API_PATH_DOCUMENT) + 1;
                $uri = Request::API_PATH_DOCUMENT . '-' . substr($uri, $documentApiPathLength);
            }

            list($uri, $originalPath, $path) = $this->getRequestPathAndUriForUri($uri);

            $this->request = new Request(null, $uri);
            $this->request->initWithPathAndOriginalPath($path, $originalPath);
            if ($this->format) {
                $this->request->format($this->format);
            }
//            fwrite(STDOUT, PHP_EOL . '-> ' . spl_object_hash($this) . ' ' . $uri . ' - ' . $this->getArgument('u', FILTER_SANITIZE_URL) . PHP_EOL);
//        } else {
//            fwrite(STDOUT, PHP_EOL . '<- ' . spl_object_hash($this) . ' ' . $this->getUri() . PHP_EOL);
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
        $this->uri = null;
        $this->format = null;

        return $this;
    }

    /**
     * Register/overwrite the current request
     *
     * @param Request $request
     * @return $this
     */
    public function registerCurrentRequest($request)
    {
        $this->resetRequest();
        $this->request = $request;

        return $this;
    }

    /**
     * Check for an alias for the given path
     *
     * @param string $path
     * @return string
     */
    public function getAliasForPath($path)
    {
        return $this->configurationProvider->getSetting('aliases.' . $path);
    }

    /**
     * Returns the URI
     *
     * @param string $format Reference to be filled with the request format
     * @return string
     */
    public function getUri(&$format = '')
    {
        if (!$this->uri) {
            $uri = $this->getArgument('u', FILTER_SANITIZE_URL);
            if (!$uri) {
                $uri = $this->removePathPrefixes($_SERVER['REQUEST_URI']);
                $uri = filter_var(substr($uri, 6), FILTER_SANITIZE_URL);
            }

            // Strip the format from the URI
            $resourceName = basename($uri);
            $lastDotPosition = strrpos($resourceName, '.');
            if ($lastDotPosition !== false) {
                $newUri = '';
                if ($uri !== $resourceName) {
                    $newUri = dirname($uri) . '/';
                }
                $newUri .= substr($resourceName, 0, $lastDotPosition);
                $uri = $newUri;

                $this->format = $format = substr($resourceName, $lastDotPosition + 1);
            }
            $this->uri = $uri;
        }

        return $this->uri;
    }

    /**
     * Returns the URI, path and original path for the given URI respecting configured aliases
     *
     * @param string $uri
     * @return string[]
     */
    protected function getRequestPathAndUriForUri($uri)
    {
        if (!$uri) {
            return array('', '', '');
        }
        $originalPath = $path = strtok(strtok($uri, '?'), '/');

        // Check for path aliases
        $pathAlias = $this->getAliasForPath($path);
        if ($pathAlias) {
            $oldPath = $path;

            // Update the URL
            $uri = preg_replace('!' . $oldPath . '!', $pathAlias, $uri, 1);
            $path = $pathAlias;
        }

        return array($uri, $originalPath, $path);
    }

    /**
     * Get a request variable
     *
     * @param string $name    Argument name
     * @param int    $filter  Filter for the input
     * @param mixed  $default Default value to use if no argument with the given name exists
     * @return mixed
     */
    protected function getArgument($name, $filter = FILTER_SANITIZE_STRING, $default = null)
    {
        $argument = GeneralUtility::_GP($name);
        $argument = filter_var($argument, $filter);
        if ($argument === null) {
            $argument = $default;
        }

        return $argument;
    }

    /**
     * Inject the configuration provider instance
     *
     * @param \Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider
     */
    public function injectConfigurationProvider(\Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @param string $uri
     * @return string
     */
    private function removePathPrefixes($uri)
    {
        $pathPrefix = getenv('TYPO3_REST_REQUEST_BASE_PATH');
        if ($pathPrefix === false) {
            $pathPrefix = $this->configurationProvider->getSetting('absRefPrefix');
        }

        return $this->removePathPrefix($uri, $pathPrefix);
    }

    /**
     * @param string $uri
     * @param string $pathPrefix
     * @return string
     */
    private function removePathPrefix($uri, $pathPrefix)
    {
        if ($pathPrefix && $pathPrefix !== 'auto' && $pathPrefix !== '/') {
            $pathPrefix = '/'. trim($pathPrefix, '/');
            if ($this->stringHasPrefix($uri, $pathPrefix)) {
                $uri = substr($uri, strlen($pathPrefix));
            }
        }

        return $uri;
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
}
