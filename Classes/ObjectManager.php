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

namespace Cundd\Rest;

use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\Authentication\CredentialsAuthenticationProvider;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface as TYPO3ObjectManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager as BaseObjectManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Specialized Object Manager
 *
 * @method mixed get($class)
 * @method bool isRegistered($class)
 */
class ObjectManager extends BaseObjectManager implements TYPO3ObjectManagerInterface, ObjectManagerInterface, SingletonInterface
{
    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @var \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    protected $authenticationProvider;

    /**
     * Configuration provider
     *
     * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @var \Cundd\Rest\Access\AccessControllerInterface
     */
    protected $accessController;

    /**
     * Returns the correct class name of the Persistence Manager for the current TYPO3 version
     *
     * @return string
     */
    public static function getPersistenceManagerClassName()
    {
        return PersistenceManagerInterface::class;
    }

    /**
     * Returns the Response Factory
     *
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory()
    {
        return $this->get(ResponseFactoryInterface::class);
    }

    /**
     * Returns the data provider
     *
     * @return DataProviderInterface
     */
    public function getDataProvider()
    {
        if (!$this->dataProvider) {
            list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType(
                $this->getRequest()->getResourceType()
            );

            $classes = [
                // Check if an extension provides a Data Provider for the domain model
                'Tx_' . $extension . '_Rest_' . $model . 'DataProvider',
                ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\' . $model . 'DataProvider',

                // Check if an extension provides a Data Provider
                'Tx_' . $extension . '_Rest_DataProvider',
                ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\DataProvider',

                // Check for a specific builtin Data Provider
                'Cundd\\Rest\\DataProvider\\' . $extension . 'DataProvider',
            ];

            $this->dataProvider = $this->get(
                $this->getFirstExistingClass($classes, DataProviderInterface::class)
            );
        }

        return $this->dataProvider;
    }

    /**
     * Returns the current request
     *
     * @return RestRequestInterface
     */
    protected function getRequest()
    {
        return $this->getRequestFactory()->getRequest();
    }

    /**
     * Returns the Request Factory
     *
     * @return RequestFactoryInterface
     */
    public function getRequestFactory()
    {
        return $this->get(RequestFactoryInterface::class);
    }

    /**
     * Returns the first of the classes that exists
     *
     * @param string[] $classes
     * @param string   $default
     * @return string
     * @throws \Exception
     */
    private function getFirstExistingClass(array $classes, $default = '')
    {
        foreach ($classes as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        if ($default === '') {
            throw new \Exception('No existing class found');
        }

        return $default;
    }

    /**
     * Returns the Authentication Provider
     *
     * @return \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    public function getAuthenticationProvider()
    {
        if (!$this->authenticationProvider) {
            list($vendor, $extension,) = Utility::getClassNamePartsForResourceType(
                $this->getRequest()->getResourceType()
            );

            // Check if an extension provides a Authentication Provider
            $authenticationProviderClass = 'Tx_' . $extension . '_Rest_AuthenticationProvider';
            if (!class_exists($authenticationProviderClass)) {
                $authenticationProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AuthenticationProvider';
            }

            // Use the found Authentication Provider
            if (class_exists($authenticationProviderClass)) {
                $this->authenticationProvider = $this->get($authenticationProviderClass);
            } else {
                // Use the default Authentication Provider
                $this->authenticationProvider = $this->get(
                    AuthenticationProviderCollection::class,
                    [
                        $this->get(BasicAuthenticationProvider::class),
                        $this->get(CredentialsAuthenticationProvider::class),
                    ]
                );
            }
        }

        return $this->authenticationProvider;
    }

    /**
     * Returns the Access Controller
     *
     * @return \Cundd\Rest\Access\AccessControllerInterface
     */
    public function getAccessController()
    {
        if (!$this->accessController) {
            list($vendor, $extension,) = Utility::getClassNamePartsForResourceType(
                $this->getRequest()->getResourceType()
            );

            // Check if an extension provides a Authentication Provider
            $accessControllerClass = 'Tx_' . $extension . '_Rest_AccessController';
            if (!class_exists($accessControllerClass)) {
                $accessControllerClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AccessController';
            }

            // Use the configuration based Authentication Provider
            if (!class_exists($accessControllerClass)) {
                $accessControllerClass = ConfigurationBasedAccessController::class;
            }
            $this->accessController = $this->get($accessControllerClass);
        }

        return $this->accessController;
    }

    /**
     * Returns the Handler which is responsible for handling the current request
     *
     * @return HandlerInterface
     */
    public function getHandler()
    {
        list($vendor, $extension,) = Utility::getClassNamePartsForResourceType($this->getRequest()->getResourceType());

        $classes = [
            // Check if an extension provides a Data Provider
            'Tx_' . $extension . '_Rest_Handler',
            ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\Handler',

            // Check for a specific builtin Data Provider
            'Cundd\\Rest\\Handler\\' . $extension . 'Handler',
        ];

        return $this->get($this->getFirstExistingClass($classes, HandlerInterface::class));
    }

    /**
     * Returns the Cache instance
     *
     * @return \Cundd\Rest\Cache\CacheInterface
     */
    public function getCache()
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $this->get(CacheFactory::class);

        return $cacheFactory->buildCache($this->getConfigurationProvider(), $this);
    }

    /**
     * Returns the configuration provider
     *
     * @return ConfigurationProviderInterface
     */
    public function getConfigurationProvider()
    {
        if (!$this->configurationProvider) {
            $this->configurationProvider = $this->get(ConfigurationProviderInterface::class);
        }

        return $this->configurationProvider;
    }

    /**
     * Resets the managed objects
     */
    public function reassignRequest()
    {
        $this->dataProvider = null;
        $this->authenticationProvider = null;
        $this->configurationProvider = null;
        $this->accessController = null;
    }
}
