<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Access\AccessControllerInterface;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Cache\CacheInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\ResourceType;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for the specialized Object Manager
 */
interface ObjectManagerInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     */
    public function has(string $class): bool;

    /**
     * Return an instance of the given class
     *
     * @template T of object
     *
     * @param class-string<T> $class The class name of the object to return an instance of
     *
     * @return T
     */
    public function get(string $class): object;

    /**
     * Return the configuration provider
     */
    public function getConfigurationProvider(
        ServerRequestInterface $request,
    ): ConfigurationProviderInterface;

    /**
     * Return the Request Factory
     */
    public function getRequestFactory(): RequestFactoryInterface;

    /**
     * Return the Response Factory
     */
    public function getResponseFactory(): ResponseFactoryInterface;

    /**
     * Return the data provider
     */
    public function getDataProvider(
        RestRequestInterface $request,
    ): DataProviderInterface;

    /**
     * Return the Authentication Provider
     */
    public function getAuthenticationProvider(
        RestRequestInterface $request,
    ): AuthenticationProviderInterface;

    /**
     * Return the Access Controller
     */
    public function getAccessController(
        RestRequestInterface $request,
    ): AccessControllerInterface;

    /**
     * Return the Handler which is responsible for handling the current request
     */
    public function getHandler(RestRequestInterface $request): HandlerInterface;

    /**
     * Return the Cache instance for the given Resource Type
     */
    public function getCache(
        RestRequestInterface $request,
        ResourceType $resourceType,
    ): CacheInterface;
}
