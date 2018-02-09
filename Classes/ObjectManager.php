<?php

namespace Cundd\Rest;

use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\Authentication\CredentialsAuthenticationProvider;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Exception\InvalidConfigurationException;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager as BaseObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface as TYPO3ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Specialized Object Manager
 *
 * @method mixed get($class)
 * @method bool isRegistered($class)
 */
class ObjectManager extends BaseObjectManager implements TYPO3ObjectManagerInterface, ObjectManagerInterface, SingletonInterface
{
    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @var \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    protected $authenticationProvider;

    /**
     * Configuration provider
     *
     * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @var \Cundd\Rest\Access\AccessControllerInterface
     */
    protected $accessController;

    /**
     * Returns the correct class name of the Persistence Manager for the current TYPO3 version
     *
     * @return string
     */
    public static function getPersistenceManagerClassName()
    {
        return PersistenceManagerInterface::class;
    }

    /**
     * Returns the Response Factory
     *
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory()
    {
        return $this->get(ResponseFactoryInterface::class);
    }

    /**
     * Returns the data provider
     *
     * @return DataProviderInterface
     */
    public function getDataProvider()
    {
        if (!$this->dataProvider) {
            list($vendor, $extension, $model) = Utility::getClassNamePartsForResourceType(
                $this->getRequest()->getResourceType()
            );

            $classes = [
                // Check if an extension provides a Data Provider for the domain model
                'Tx_' . $extension . '_Rest_' . $model . 'DataProvider',
                ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\' . $model . 'DataProvider',

                // Check if an extension provides a Data Provider
                'Tx_' . $extension . '_Rest_DataProvider',
                ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\DataProvider',

                // Check for a specific builtin Data Provider
                'Cundd\\Rest\\DataProvider\\' . $extension . 'DataProvider',
            ];

            $this->dataProvider = $this->get(
                $this->getFirstExistingClass($classes, DataProviderInterface::class)
            );
        }

        return $this->dataProvider;
    }

    /**
     * Returns the current request
     *
     * @return RestRequestInterface
     */
    protected function getRequest()
    {
        return $this->getRequestFactory()->getRequest();
    }

    /**
     * Returns the Request Factory
     *
     * @return RequestFactoryInterface
     */
    public function getRequestFactory()
    {
        return $this->get(RequestFactoryInterface::class);
    }

    /**
     * Returns the first of the classes that exists
     *
     * @param string[] $classes
     * @param string   $default
     * @return string
     * @throws \Exception
     */
    private function getFirstExistingClass(array $classes, $default = '')
    {
        foreach ($classes as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        if ($default === '') {
            throw new \Exception('No existing class found');
        }

        return $default;
    }

    /**
     * Returns the Authentication Provider
     *
     * @return \Cundd\Rest\Authentication\AuthenticationProviderInterface
     */
    public function getAuthenticationProvider()
    {
        if (!$this->authenticationProvider) {
            list($vendor, $extension,) = Utility::getClassNamePartsForResourceType(
                $this->getRequest()->getResourceType()
            );

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
                $this->authenticationProvider = call_user_func(
                    [$this, 'get'],
                    AuthenticationProviderCollection::class,
                    [
                        $this->get(BasicAuthenticationProvider::class),
                        $this->get(CredentialsAuthenticationProvider::class),
                    ]
                );
//                $this->authenticationProvider = $this->get(
//                    AuthenticationProviderCollection::class,
//                    [
//                        $this->get(BasicAuthenticationProvider::class),
//                        $this->get(CredentialsAuthenticationProvider::class),
//                    ]
//                );
            }
        }

        return $this->authenticationProvider;
    }

    /**
     * Returns the Access Controller
     *
     * @return \Cundd\Rest\Access\AccessControllerInterface
     */
    public function getAccessController()
    {
        if (!$this->accessController) {
            list($vendor, $extension,) = Utility::getClassNamePartsForResourceType(
                $this->getRequest()->getResourceType()
            );

            // Check if an extension provides a Authentication Provider
            $accessControllerClass = 'Tx_' . $extension . '_Rest_AccessController';
            if (!class_exists($accessControllerClass)) {
                $accessControllerClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AccessController';
            }

            // Use the configuration based Authentication Provider
            if (!class_exists($accessControllerClass)) {
                $accessControllerClass = ConfigurationBasedAccessController::class;
            }
            $this->accessController = $this->get($accessControllerClass);
        }

        return $this->accessController;
    }

    /**
     * Returns the Handler which is responsible for handling the current request
     *
     * @return HandlerInterface
     */
    public function getHandler()
    {
        $resourceType = $this->getRequest()->getResourceType();
        $handler = $this->getHandlerFromResourceConfiguration($resourceType);
        if ($handler) {
            return $handler;
        }

        list($vendor, $extension,) = Utility::getClassNamePartsForResourceType($resourceType);

        $classes = [
            // Check if an extension provides a Data Provider
            'Tx_' . $extension . '_Rest_Handler',
            ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\Handler',

            // Check for a specific builtin Handler
            // @deprecated register a `handlerClass` instead
            'Cundd\\Rest\\Handler\\' . $extension . 'Handler',
        ];

        return $this->get($this->getFirstExistingClass($classes, HandlerInterface::class));
    }

    /**
     * Returns the Cache instance
     *
     * @return \Cundd\Rest\Cache\CacheInterface
     */
    public function getCache()
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $this->get(CacheFactory::class);

        return $cacheFactory->buildCache($this->getConfigurationProvider(), $this);
    }

    /**
     * Returns the configuration provider
     *
     * @return ConfigurationProviderInterface
     */
    public function getConfigurationProvider()
    {
        if (!$this->configurationProvider) {
            $this->configurationProvider = $this->get(ConfigurationProviderInterface::class);
        }

        return $this->configurationProvider;
    }

    /**
     * Resets the managed objects
     */
    public function reassignRequest()
    {
        $this->dataProvider = null;
        $this->authenticationProvider = null;
        $this->configurationProvider = null;
        $this->accessController = null;
    }

    /**
     * @param ResourceType $resourceType
     * @return HandlerInterface|null
     */
    private function getHandlerFromResourceConfiguration(ResourceType $resourceType)
    {
        $resourceConfiguration = $this->getConfigurationProvider()->getResourceConfiguration($resourceType);
        if (!$resourceConfiguration) {
            // This case should not occur in reality, since at least the `all` Resource should have been configured
            throw new InvalidConfigurationException(
                sprintf('Resource "%s" is not configured', (string)$resourceType)
            );
        }
        $handlerClass = $resourceConfiguration->getHandlerClass();
        if (!$handlerClass) {
            return null;
        }

        if (!class_exists($handlerClass)) {
            throw new InvalidConfigurationException(
                sprintf('Configured Handler "%s" does not exist', $handlerClass)
            );
        }

        if ($handlerClass[0] === '\\') {
            $handlerClass = substr($handlerClass, 1);
        }

        return $this->get($handlerClass);
    }
}
