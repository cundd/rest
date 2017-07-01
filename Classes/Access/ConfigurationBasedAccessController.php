<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

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
        $configurationKey = self::ACCESS_METHOD_READ;
        $configuration = $this->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        if ($this->isWrite($request)) {
            $configurationKey = self::ACCESS_METHOD_WRITE;
        }

        // Throw an exception if the configuration is not complete
        if (!isset($configuration[$configurationKey])) {
            throw new InvalidConfigurationException($configurationKey . ' configuration not set', 1376826223);
        }

        $access = $configuration[$configurationKey];
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
        $matchingConfiguration = array();
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

        return isset($settings['paths.']) ? $settings['paths.'] : array();
    }

    /**
     * Returns if the request wants to write data
     *
     * @param RestRequestInterface $request
     * @return bool
     */
    protected function isWrite(RestRequestInterface $request)
    {
        return $request->isWrite();
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
        $configurationKey = self::ACCESS_METHOD_READ;
        $configuration = $this->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        if ($this->isWrite($request)) {
            $configurationKey = self::ACCESS_METHOD_WRITE;
        }

        // Throw an exception if the configuration is not complete
        if (!isset($configuration[$configurationKey])) {
            throw new InvalidConfigurationException($configurationKey . ' configuration not set', 1376826223);
        }

        $access = $configuration[$configurationKey];
        if ($access === AccessControllerInterface::ACCESS_REQUIRE_LOGIN) {
            return true;
        }

        return false;
    }
}
