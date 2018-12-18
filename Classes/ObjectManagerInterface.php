<?php
declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Handler\HandlerInterface;

/**
 * Interface for the specialized Object Manager
 */
interface ObjectManagerInterface
{
    /**
     * Return an instance of the given class
     *
     * @param string $class The class name of the object to return an instance of
     * @param array  $arguments
     * @return object The object instance
     */
    public function get($class, ...$arguments);

    /**
     * Returns the configuration provider
     *
     * @return \Cundd\Rest\Configuration\ConfigurationProviderInterface
     */
    public function getConfigurationProvider();

    /**
     * Returns the configuration provider
     *
     * @return RequestFactoryInterface
     */
    public function getRequestFactory();

    /**
     * Returns the Response Factory
     *
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory();

    /**
     * Returns the data provider
     *
     * @return \Cundd\Rest\DataProvider\DataProviderInterface
     */
    public function getDataProvider();

    /**
     * Returns the Authentication Provider
     *
     * @return \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    public function getAuthenticationProvider();

    /**
     * Returns the Access Controller
     *
     * @return \Cundd\Rest\Access\AccessControllerInterface
     */
    public function getAccessController();

    /**
     * Returns the Handler which is responsible for handling the current request
     *
     * @return HandlerInterface
     */
    public function getHandler();

    /**
     * Returns the Cache instance
     *
     * @return \Cundd\Rest\Cache\CacheInterface
     */
    public function getCache();
}
