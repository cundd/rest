<?php

namespace Cundd\Rest;

use Cundd\Rest\Access\AccessControllerInterface;
use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Cache\CacheInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
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
     * @var AuthenticationProviderInterface
     */
    protected $authenticationProvider;

    /**
     * Configuration provider
     *
     * @var TypoScriptConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @var AccessControllerInterface
     */
    protected $accessController;

    /**
     * Returns the correct class name of the Persistence Manager for the current TYPO3 version
     *
     * @return string
     * @deprecated will be removed in 4.0.0
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
                sprintf('Tx_%s_Rest_%sDataProvider', $extension, $model),
                sprintf('%s%s\\Rest\\%sDataProvider', ($vendor ? $vendor . '\\' : ''), $extension, $model),

                // Check if an extension provides a Data Provider
                sprintf('Tx_%s_Rest_DataProvider', $extension),
                sprintf('%s%s\\Rest\\DataProvider', ($vendor ? $vendor . '\\' : ''), $extension),

                // Check for a specific builtin Data Provider
                sprintf('Cundd\\Rest\\DataProvider\\%sDataProvider', $extension),
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
     * @return AuthenticationProviderInterface
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
                // Use the Authentication Providers defined in TypoScript
                $providerInstances = [];
                $configuredProviders = $this->getConfigurationProvider()->getSetting('authenticationProvider');
                ksort($configuredProviders);
                foreach ($configuredProviders as $providerClass) {
                    if (class_exists($providerClass)) {
                        $providerInstances[] = $this->get(ltrim($providerClass, '\\'));
                    }
                }

                $this->authenticationProvider = call_user_func(
                    [$this, 'get'],
                    AuthenticationProviderCollection::class,
                    $providerInstances
                );
            }
        }

        return $this->authenticationProvider;
    }

    /**
     * Returns the Access Controller
     *
     * @return AccessControllerInterface
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
            sprintf('Tx_' . $extension . '_Rest_Handler'),
            sprintf('%s%s\\Rest\\Handler', ($vendor ? $vendor . '\\' : ''), $extension),

            // Check for a specific builtin Handler
            // @deprecated register a `handlerClass` instead
            'Cundd\\Rest\\Handler\\' . $extension . 'Handler',
        ];

        return $this->get($this->getFirstExistingClass($classes, HandlerInterface::class));
    }

    /**
     * Returns the Cache instance for the given Resource Type
     *
     * @param ResourceType $resourceType
     * @return CacheInterface
     */
    public function getCache(ResourceType $resourceType)
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $this->get(CacheFactory::class);

        return $cacheFactory->buildCache($resourceType, $this->getConfigurationProvider(), $this);
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
