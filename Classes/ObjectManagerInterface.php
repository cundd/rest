<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Access\AccessControllerInterface;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Cache\CacheInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;

/**
 * Interface for the specialized Object Manager
 */
interface ObjectManagerInterface
{
    /**
     * Return an instance of the given class
     *
     * @param string $class The class name of the object to return an instance of
     * @return object The object instance
     */
    public function get(string $class): object;

    /**
     * Return the configuration provider
     *
     * @return ConfigurationProviderInterface
     */
    public function getConfigurationProvider(): ConfigurationProviderInterface;

    /**
     * Return the configuration provider
     *
     * @return RequestFactoryInterface
     */
    public function getRequestFactory(): RequestFactoryInterface;

    /**
     * Return the Response Factory
     *
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory(): ResponseFactoryInterface;

    /**
     * Return the data provider
     *
     * @param RestRequestInterface $request
     * @return DataProviderInterface
     */
    public function getDataProvider(RestRequestInterface $request): DataProviderInterface;

    /**
     * Return the Authentication Provider
     *
     * @param RestRequestInterface $request
     * @return AuthenticationProviderInterface
     */
    public function getAuthenticationProvider(RestRequestInterface $request): AuthenticationProviderInterface;

    /**
     * Return the Access Controller
     *
     * @param RestRequestInterface $request
     * @return AccessControllerInterface
     */
    public function getAccessController(RestRequestInterface $request): AccessControllerInterface;

    /**
     * Return the Handler which is responsible for handling the current request
     *
     * @param RestRequestInterface $request
     * @return HandlerInterface
     */
    public function getHandler(RestRequestInterface $request): HandlerInterface;

    /**
     * Return the Cache instance for the given Resource Type
     *
     * @param ResourceType $resourceType
     * @return CacheInterface
     */
    public function getCache(ResourceType $resourceType): CacheInterface;
}
