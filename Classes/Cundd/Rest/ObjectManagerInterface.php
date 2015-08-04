<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 03.08.15
 * Time: 19:54
 */

namespace Cundd\Rest;

/**
 * Interface for the specialized Object Manager
 * @package Cundd\Rest
 */
interface ObjectManagerInterface {
    /**
     * Returns the configuration provider
     *
     * @return \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     */
    public function getConfigurationProvider();

    /**
     * Returns the configuration provider
     *
     * @return \Cundd\Rest\RequestFactory
     */
    public function getRequestFactory();

    /**
     * Returns the data provider
     *
     * @throws Exception if the dispatcher is not set
     * @return \Cundd\Rest\DataProvider\DataProviderInterface
     */
    public function getDataProvider();

    /**
     * Returns the Authentication Provider
     * @return \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    public function getAuthenticationProvider();

    /**
     * Returns the Access Controller
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
     * @return \Cundd\Rest\Cache\Cache
     */
    public function getCache();
}