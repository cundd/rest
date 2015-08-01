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


use Cundd\Rest\DataProvider\Utility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use \TYPO3\CMS\Extbase\Object\ObjectManager as BaseObjectManager;


class ObjectManager extends BaseObjectManager implements ObjectManagerInterface, SingletonInterface {
    /**
     * @var \Cundd\Rest\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var \Cundd\Rest\DataProvider\DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @var \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    protected $authenticationProvider;

    /**
     * Configuration provider
     * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @var \Cundd\Rest\Access\AccessControllerInterface
     */
    protected $accessController;

    /**
     * Injects the dispatcher
     *
     * @param \Cundd\Rest\Dispatcher $dispatcher
     */
    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns the configuration provider
     *
     * @return \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     */
    public function getConfigurationProvider() {
        if (!$this->configurationProvider) {
            $this->configurationProvider = $this->get('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider');
        }
        return $this->configurationProvider;
    }

    /**
     * Returns the data provider
     *
     * @throws Exception if the dispatcher is not set
     * @return \Cundd\Rest\DataProvider\DataProviderInterface
     */
    public function getDataProvider() {
        if (!$this->dataProvider) {
            /** @var Dispatcher $dispatcher */
            $dispatcher = $this->dispatcher ? $this->dispatcher : Dispatcher::getSharedDispatcher();
            list($vendor, $extension,) = Utility::getClassNamePartsForPath($dispatcher->getPath());

            // Check if an extension provides a Data Provider
            $dataProviderClass = 'Tx_' . $extension . '_Rest_DataProvider';
            if (!class_exists($dataProviderClass)) {
                $dataProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\DataProvider';
            }
            // Get the specific builtin Data Provider
            if (!class_exists($dataProviderClass)) {
                $dataProviderClass = 'Cundd\\Rest\\DataProvider\\' . $extension . 'DataProvider';
                // Get the default Data Provider
                if (!class_exists($dataProviderClass)) {
                    $dataProviderClass = 'Cundd\\Rest\\DataProvider\\DataProviderInterface';
                }
            }
            $this->dataProvider = $this->get($dataProviderClass);
        }
        return $this->dataProvider;
    }

    /**
     * Returns the Authentication Provider
     * @return \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    public function getAuthenticationProvider() {
        if (!$this->authenticationProvider) {
            /** @var Dispatcher $dispatcher */
            $dispatcher = $this->dispatcher ? $this->dispatcher : Dispatcher::getSharedDispatcher();
            list($vendor, $extension,) = Utility::getClassNamePartsForPath($dispatcher->getPath());

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
                $this->authenticationProvider = $this->get('Cundd\\Rest\\Authentication\\AuthenticationProviderCollection', array(
                    $this->get('Cundd\\Rest\\Authentication\\BasicAuthenticationProvider'),
                    $this->get('Cundd\\Rest\\Authentication\\CredentialsAuthenticationProvider'),
                ));
            }

            $this->authenticationProvider->setRequest($dispatcher->getRequest());
        }
        return $this->authenticationProvider;
    }

    /**
     * Returns the Access Controller
     * @return \Cundd\Rest\Access\AccessControllerInterface
     */
    public function getAccessController() {
        if (!$this->accessController) {
            list($vendor, $extension,) = Utility::getClassNamePartsForPath($this->dispatcher->getPath());

            // Check if an extension provides a Authentication Provider
            $accessControllerClass = 'Tx_' . $extension . '_Rest_AccessController';
            if (!class_exists($accessControllerClass)) {
                $accessControllerClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AccessController';
            }

            // Use the configuration based Authentication Provider
            if (!class_exists($accessControllerClass)) {
                $accessControllerClass = 'Cundd\\Rest\\Access\\ConfigurationBasedAccessController';
            }
            $this->accessController = $this->get($accessControllerClass);
            $this->accessController->setRequest($this->dispatcher->getRequest());
        }
        return $this->accessController;
    }

    /**
     * Returns the Handler which is responsible for handling the current request
     *
     * @return HandlerInterface
     */
    public function getHandler() {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->dispatcher ? $this->dispatcher : Dispatcher::getSharedDispatcher();

        /** @var \Cundd\Rest\HandlerInterface $handler */
        list($vendor, $extension,) = Utility::getClassNamePartsForPath($dispatcher->getPath());

        // Check if an extension provides a Handler
        $handlerClass = 'Tx_' . $extension . '_Rest_Handler';
        if (!class_exists($handlerClass)) {
            $handlerClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\Handler';
        }

        // Get the specific builtin handler
        if (!class_exists($handlerClass)) {
            $handlerClass = 'Cundd\\Rest\\Handler\\' . $extension . 'Handler';
            // Get the default handler
            if (!class_exists($handlerClass)) {
                $handlerClass = 'Cundd\\Rest\\HandlerInterface';
            }
        }
        return $this->get($handlerClass);
    }

    /**
     * Returns the correct class name of the Persistence Manager for the current TYPO3 version
     *
     * @return string
     */
    static public function getPersistenceManagerClassName() {
        if (version_compare(TYPO3_version, '6.0.0') < 0) {
            return 'Tx_Extbase_Persistence_Manager';
        }
        return 'TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface';
    }

    /**
     * Returns the Cache instance
     *
     * @return \Cundd\Rest\Cache\Cache
     */
    public function getCache() {
        return $this->get('Cundd\\Rest\\Cache\\Cache');
    }

    /**
     * Resets the managed objects
     */
    public function reassignRequest() {
        $request = $this->dispatcher->getRequest();
        if ($this->authenticationProvider) {
            $this->authenticationProvider->setRequest($request);
        }
        if ($this->accessController) {
            $this->accessController->setRequest($request);
        }
    }
}
