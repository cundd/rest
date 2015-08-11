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

namespace Cundd\Rest\Authentication;


/**
 * Authentication Provider that tests a collection of Authentication Providers
 *
 * @package Cundd\Rest\Authentication
 */
class AuthenticationProviderCollection implements AuthenticationProviderInterface {
    /**
     * Collection of Authentication Providers
     *
     * @var \SplObjectStorage<AuthenticationProviderInterface>
     */
    protected $providers;

    /**
     * Create a new Authentication Provider collection with the given providers
     *
     * @param array <AuthenticationProviderInterface>|\SplObjectStorage<AuthenticationProviderInterface> $providers
     */
    function __construct($providers) {
        $this->providers = new \SplObjectStorage();
        if (is_array($providers)) {
            foreach ($providers as $provider) {
                $this->addProvider($provider);
            }
        } else if ($providers instanceof \SplObjectStorage) {
            $this->providers = $providers;
        }
    }

    /**
     * Loops through each Authentication Provider in the collection and tries to authenticate the current request
     *
     * @return bool Returns if the authentication was successful
     */
    public function authenticate() {
        /** @var AuthenticationProviderInterface $authenticationProvider */
        foreach ($this->providers as $authenticationProvider) {
            if ($authenticationProvider->authenticate()) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * @param \Bullet\Request|\Cundd\Rest\Request $request
     * @return mixed|void
     */
    public function setRequest(\Cundd\Rest\Request $request) {
        /** @var AuthenticationProviderInterface $authenticationProvider */
        foreach ($this->providers as $authenticationProvider) {
            $authenticationProvider->setRequest($request);
        }
    }

    /**
     * Sets the used Authentication Providers
     *
     * @param \SplObjectStorage $providers
     * @return $this
     */
    public function setProviders($providers) {
        $this->providers = $providers;
        return $this;
    }

    /**
     * Returns the used Authentication Providers
     *
     * @return \SplObjectStorage
     */
    public function getProviders() {
        return $this->providers;
    }

    /**
     * Adds the given Authentication Provider
     *
     * @param AuthenticationProviderInterface $provider
     * @return $this
     */
    public function addProvider(AuthenticationProviderInterface $provider) {
        $this->providers->attach($provider);
        return $this;
    }

    /**
     * Removes the given Authentication Provider
     *
     * @param AuthenticationProviderInterface $provider
     * @return $this
     */
    public function removeProvider(AuthenticationProviderInterface $provider) {
        $this->providers->detach($provider);
        return $this;
    }
}
