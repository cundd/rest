<?php

namespace Cundd\Rest\Access;

use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
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
     * @param RestRequestInterface $request
     * @return Access
     * @throws \Exception
     */
    public function getAccess(RestRequestInterface $request)
    {
        $access = $this->getAccessConfiguration($request);
        if ($access->isRequireLogin()) {
            return $this->checkAuthentication($request);
        }

        return $access;
    }

    /**
     * Returns the configuration matching the given resource type
     *
     * @param ResourceType $resourceType
     * @return ResourceConfiguration
     */
    public function getConfigurationForResourceType(ResourceType $resourceType)
    {
        $configuredPaths = $this->configurationProvider->getConfiguredResourceTypes();
        $matchingConfiguration = null;
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
            $currentPath = (string)$configuration->getResourceType();

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
     * Returns if the given request needs authentication
     *
     * @param RestRequestInterface $request
     * @return bool
     * @throws Exception\InvalidConfigurationException
     */
    public function requestNeedsAuthentication(RestRequestInterface $request)
    {
        return $this->getAccessConfiguration($request)->isRequireLogin();
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
     * @return Access
     */
    protected function getAccessConfiguration(RestRequestInterface $request)
    {
        if (!$this->requiresAuthorization($request)) {
            return Access::allowed();
        }

        $configuration = $this->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        if ($request->isWrite()) {
            return $configuration->getWrite();
        }

        return $configuration->getRead();
    }
}
