<?php

namespace Cundd\Rest;

use Cundd\Rest\Cache\CacheInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Handler\HandlerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface as TYPO3ObjectManagerInterface;

/**
 * Interface for the specialized Object Manager
 */
interface ObjectManagerInterface extends TYPO3ObjectManagerInterface
{
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
     * Returns the Cache instance for the given Resource Type
     *
     * @param ResourceType $resourceType
     * @return CacheInterface|object
     */
    public function getCache(ResourceType $resourceType);
}
