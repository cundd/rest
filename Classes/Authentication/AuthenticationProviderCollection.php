<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;
use SplObjectStorage;

/**
 * Authentication Provider that tests a collection of Authentication Providers
 */
class AuthenticationProviderCollection implements AuthenticationProviderInterface
{
    /**
     * Collection of Authentication Providers
     *
     * @var SplObjectStorage<AuthenticationProviderInterface>
     */
    protected SplObjectStorage $providers;

    /**
     * Create a new Authentication Provider collection with the given providers
     *
     * @param AuthenticationProviderInterface[]|SplObjectStorage<AuthenticationProviderInterface> $providers
     */
    public function __construct($providers)
    {
        if (is_array($providers)) {
            $this->providers = new SplObjectStorage();
            foreach ($providers as $provider) {
                $this->addProvider($provider);
            }
        } elseif ($providers instanceof SplObjectStorage) {
            $this->providers = $providers;
        } else {
            $this->providers = new SplObjectStorage();
        }
    }

    /**
     * Loops through each Authentication Provider in the collection and tries to authenticate the current request
     *
     * @param RestRequestInterface $request
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request): bool
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
     * @param SplObjectStorage $providers
     * @return $this
     */
    public function setProviders(SplObjectStorage $providers): self
    {
        $this->providers = $providers;

        return $this;
    }

    /**
     * Returns the used Authentication Providers
     *
     * @return SplObjectStorage
     */
    public function getProviders(): SplObjectStorage
    {
        return $this->providers;
    }

    /**
     * Adds the given Authentication Provider
     *
     * @param AuthenticationProviderInterface $provider
     * @return $this
     */
    public function addProvider(AuthenticationProviderInterface $provider): self
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
    public function removeProvider(AuthenticationProviderInterface $provider): self
    {
        $this->providers->detach($provider);

        return $this;
    }
}
