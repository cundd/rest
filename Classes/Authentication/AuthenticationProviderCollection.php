<?php

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;

/**
 * Authentication Provider that tests a collection of Authentication Providers
 */
class AuthenticationProviderCollection implements AuthenticationProviderInterface
{
    /**
     * Collection of Authentication Providers
     *
     * @var \SplObjectStorage<AuthenticationProviderInterface>
     */
    protected $providers;

    /**
     * Create a new Authentication Provider collection with the given providers
     *
     * @param AuthenticationProviderInterface[]|\SplObjectStorage<AuthenticationProviderInterface> $providers
     */
    public function __construct($providers)
    {
        $this->providers = new \SplObjectStorage();
        if (is_array($providers)) {
            foreach ($providers as $provider) {
                $this->addProvider($provider);
            }
        } elseif ($providers instanceof \SplObjectStorage) {
            $this->providers = $providers;
        }
    }

    /**
     * Loops through each Authentication Provider in the collection and tries to authenticate the current request
     *
     * @param RestRequestInterface $request
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request)
    {
        /** @var AuthenticationProviderInterface $authenticationProvider */
        foreach ($this->providers as $authenticationProvider) {
            if ($authenticationProvider->authenticate($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the used Authentication Providers
     *
     * @param \SplObjectStorage $providers
     * @return $this
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;

        return $this;
    }

    /**
     * Returns the used Authentication Providers
     *
     * @return \SplObjectStorage
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Adds the given Authentication Provider
     *
     * @param AuthenticationProviderInterface $provider
     * @return $this
     */
    public function addProvider(AuthenticationProviderInterface $provider)
    {
        $this->providers->attach($provider);

        return $this;
    }

    /**
     * Removes the given Authentication Provider
     *
     * @param AuthenticationProviderInterface $provider
     * @return $this
     */
    public function removeProvider(AuthenticationProviderInterface $provider)
    {
        $this->providers->detach($provider);

        return $this;
    }
}
