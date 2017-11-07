<?php

namespace Cundd\Rest\Access;

use Cundd\Rest\Access\Exception\InvalidConfigurationException;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManager;

/**
 * The class determines the access for the current request
 */
class ConfigurationBasedAccessController extends AbstractAccessController
{
    /**
     * The request want's to read data
     */
    const ACCESS_METHOD_READ = 'read';

    /**
     * The request want's to write data
     */
    const ACCESS_METHOD_WRITE = 'write';

    /**
     * Access identifier to specify which methods DO NOT require authorization
     */
    const ACCESS_NOT_REQUIRED = ['OPTIONS'];

    /**
     * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * ConfigurationBasedAccessController constructor
     *
     * @param ConfigurationProviderInterface $configurationProvider
     * @param ObjectManager                  $objectManager
     */
    public function __construct(ConfigurationProviderInterface $configurationProvider, ObjectManager $objectManager)
    {
        parent::__construct($objectManager);
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * Returns if the current request's client has access to the requested resource
     *
     * @param RestRequestInterface $request
     * @return string Returns one of the constants AccessControllerInterface::ACCESS
     */
    public function getAccess(RestRequestInterface $request)
    {
        $access = $this->getAccessConfiguration($request);
        if ($access === AccessControllerInterface::ACCESS_REQUIRE_LOGIN) {
            return $this->checkAuthentication($request);
        }

        return $access;
    }

    /**
     * Returns the configuration matching the given resource type
     *
     * @param ResourceType $resourceType
     * @return array
     */
    public function getConfigurationForResourceType(ResourceType $resourceType)
    {
        $configuredPaths = $this->getConfiguredResourceTypes();
        $matchingConfiguration = [];
        $resourceTypeString = Utility::normalizeResourceType($resourceType);

        if (!$resourceTypeString) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid normalized Resource Type "%s"',
                    is_null($resourceTypeString) ? 'null' : $resourceTypeString
                )
            );
        }


        foreach ($configuredPaths as $configuration) {
            $currentPath = $configuration['path'];

            $currentPathPattern = str_replace('*', '\w*', str_replace('?', '\w', $currentPath));
            $currentPathPattern = "!^$currentPathPattern$!";
            if ($currentPath === 'all' && !$matchingConfiguration) {
                $matchingConfiguration = $configuration;
            } elseif (preg_match($currentPathPattern, $resourceTypeString)) {
                $matchingConfiguration = $configuration;
            }
        }

        return $matchingConfiguration;
    }

    /**
     * Returns the paths configured in the settings
     *
     * @return array
     */
    private function getConfiguredResourceTypes()
    {
        $configurationCollection = [];
        foreach ($this->getRawConfiguredResourceTypes() as $path => $configuration) {
            // If no explicit path is configured use the current key
            $resourceType = isset($configuration['path']) ? $configuration['path'] : trim($path, '.');
            $normalizeResourceType = Utility::normalizeResourceType($resourceType);
            $configuration['path'] = $normalizeResourceType;

            $configurationCollection[$normalizeResourceType] = $configuration;
        }

        return $configurationCollection;
    }

    /**
     * @return array
     */
    private function getRawConfiguredResourceTypes()
    {
        $settings = $this->configurationProvider->getSettings();
        if (isset($settings['paths']) && is_array($settings['paths'])) {
            return $settings['paths'];
        }

        return isset($settings['paths.']) ? $settings['paths.'] : [];
    }

    /**
     * Returns if the given request needs authentication
     *
     * @param RestRequestInterface $request
     * @return bool
     * @throws Exception\InvalidConfigurationException
     */
    public function requestNeedsAuthentication(RestRequestInterface $request)
    {
        if ($this->getAccessConfiguration($request) === AccessControllerInterface::ACCESS_REQUIRE_LOGIN) {
            return true;
        }

        return false;
    }

    /**
     * Returns if the given request requires authorization
     *
     * @param RestRequestInterface $request
     * @return bool
     */
    protected function requiresAuthorization($request)
    {
        return !in_array(strtoupper($request->getMethod()), self::ACCESS_NOT_REQUIRED);
    }

    /**
     * @param RestRequestInterface $request
     * @return string
     */
    protected function getAccessConfiguration(RestRequestInterface $request)
    {
        if (!$this->requiresAuthorization($request)) {
            return AccessControllerInterface::ACCESS_ALLOW;
        }

        $configurationKey = $request->isWrite()
            ? self::ACCESS_METHOD_WRITE
            : self::ACCESS_METHOD_READ;

        $configuration = $this->getConfigurationForResourceType(new ResourceType($request->getResourceType()));

        // Throw an exception if the configuration is not complete
        if (!isset($configuration[$configurationKey])) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Configuration "%s" not set for Resource Type "%s"',
                    $configurationKey,
                    $request->getResourceType()
                ), 1376826223
            );
        }

        return (string)$configuration[$configurationKey];
    }
}
