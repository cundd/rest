<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 03.08.15
 * Time: 19:54
 */

namespace Cundd\Rest;

use TYPO3\CMS\Extbase\Object\ObjectManagerInterface as TYPO3ObjectManagerInterface;

/**
 * Interface for the specialized Object Manager
 */
interface ObjectManagerInterface extends TYPO3ObjectManagerInterface
{
    /**
     * Returns the configuration provider
     *
     * @return \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
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
