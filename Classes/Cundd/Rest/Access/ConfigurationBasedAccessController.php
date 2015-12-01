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

/**
 * The class determines the access for the current request
 *
 * @package Cundd\Rest\Access
 */
class ConfigurationBasedAccessController extends AbstractAccessController {
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
     * Inject the configuration provider
     *
     * @param \Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider
     */
    public function injectConfigurationProvider(\Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider) {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * Returns if the current request has access to the requested resource
     *
     * @throws Exception\InvalidConfigurationException if the configuration is incomplete
     * @return AccessControllerInterface::ACCESS
     */
    public function getAccess() {
        $configurationKey = self::ACCESS_METHOD_READ;
        $configuration = $this->getConfigurationForCurrentPath();
        if ($this->isWrite()) {
            $configurationKey = self::ACCESS_METHOD_WRITE;
        }

        // Throw an exception if the configuration is not complete
        if (!isset($configuration[$configurationKey])) {
            throw new InvalidConfigurationException($configurationKey . ' configuration not set', 1376826223);
        }

        $access = $configuration[$configurationKey];
        if ($access === AccessControllerInterface::ACCESS_REQUIRE_LOGIN) {
            return $this->checkAuthentication();
        }
        return $access;
    }

    /**
     * Returns if the given request needs authentication
     *
     * @return bool
     * @throws Exception\InvalidConfigurationException
     */
    public function requestNeedsAuthentication() {
        $configurationKey = self::ACCESS_METHOD_READ;
        $configuration = $this->getConfigurationForCurrentPath();
        if ($this->isWrite()) {
            $configurationKey = self::ACCESS_METHOD_WRITE;
        }

        // Throw an exception if the configuration is not complete
        if (!isset($configuration[$configurationKey])) {
            throw new InvalidConfigurationException($configurationKey . ' configuration not set', 1376826223);
        }

        $access = $configuration[$configurationKey];
        if ($access === AccessControllerInterface::ACCESS_REQUIRE_LOGIN) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Sets the current request
     *
     * @param \Cundd\Rest\Request $request
     */
    public function setRequest(\Cundd\Rest\Request $request) {
        parent::setRequest($request);
    }


    /**
     * Returns if the request wants to write data
     *
     * @return bool
     */
    public function isWrite() {
        return $this->request->isWrite();
    }

    /**
     * Returns the configuration matching the current request's path
     *
     * @return string
     * @throws \UnexpectedValueException if the request is not set
     */
    public function getConfigurationForCurrentPath() {
        if (!$this->request) {
            throw new \UnexpectedValueException('The request isn\'t set', 1376816053);
        }
        return $this->getConfigurationForPath($this->request->path());
    }

    /**
     * Returns the configuration matching the given request path
     *
     * @param string $path
     * @return string
     */
    public function getConfigurationForPath($path) {
        $configuredPaths = $this->getConfiguredPaths();
        $matchingConfiguration = array();

        foreach ($configuredPaths as $configuration) {
            $currentPath = $configuration['path'];

            $currentPathPattern = str_replace('*', '\w*', str_replace('?', '\w', $currentPath));
            $currentPathPattern = "!^$currentPathPattern$!";
            if ($currentPath === 'all' && !$matchingConfiguration) {
                $matchingConfiguration = $configuration;
            } else if (preg_match($currentPathPattern, $path)) {
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
    public function getConfiguredPaths() {
        $settings = $this->configurationProvider->getSettings();
        if (isset($settings['paths']) && is_array($settings['paths'])) {
            return $settings['paths'];
        }
        return isset($settings['paths.']) ? $settings['paths.'] : array();
    }
}
