<?php
declare(strict_types=1);

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
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager as TYPO3ObjectManager;

/**
 * Specialized Object Manager
 *
 * @method bool isRegistered($class)
 */
class ObjectManager implements ObjectManagerInterface, SingletonInterface
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
     * @var ContainerInterface|\TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $container;

    /**
     * Object Manager constructor
     *
     * @param ContainerInterface|\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $container
     */
    public function __construct($container = null)
    {
        $this->container = $container ?: GeneralUtility::makeInstance(TYPO3ObjectManager::class);
    }

    public function get($class, ...$arguments)
    {
        return $this->container->get($class, ...$arguments);
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->get(ResponseFactoryInterface::class);
    }

    public function getDataProvider(): DataProviderInterface
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

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->get(RequestFactoryInterface::class);
    }

    public function getAuthenticationProvider(): AuthenticationProviderInterface
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

    public function getAccessController(): AccessControllerInterface
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

    public function getHandler(): HandlerInterface
    {
        $resourceType = $this->getRequest()->getResourceType();
        $handler = $this->getHandlerFromResourceConfiguration($resourceType);
        if ($handler) {
            return $handler;
        }

        list($vendor, $extension,) = Utility::getClassNamePartsForResourceType($resourceType);

        $classes = [
            // Check if an extension provides a Handler
            // @deprecated register a `handlerClass` instead
            sprintf('%s%s\\Rest\\Handler', ($vendor ? $vendor . '\\' : ''), $extension),
            sprintf('Tx_' . $extension . '_Rest_Handler'),

            // Check for a specific builtin Handler
            'Cundd\\Rest\\Handler\\' . $extension . 'Handler',
            CrudHandler::class,
        ];

        return $this->get($this->getFirstExistingClass($classes, HandlerInterface::class));
    }

    public function getCache(ResourceType $resourceType): CacheInterface
    {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $this->get(CacheFactory::class);

        return $cacheFactory->buildCache($resourceType, $this->getConfigurationProvider(), $this);
    }

    public function getConfigurationProvider(): ConfigurationProviderInterface
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
     * Returns the current request
     *
     * @return RestRequestInterface
     */
    protected function getRequest()
    {
        return $this->getRequestFactory()->getRequest();
    }

    /**
     * Returns the first of the classes that exists
     *
     * @param string[] $classes
     * @param string   $default
     * @return string
     * @throws \LogicException
     */
    private function getFirstExistingClass(array $classes, $default = '')
    {
        foreach ($classes as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        if ($default === '') {
            throw new \LogicException('No existing class found');
        }

        return $default;
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

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }
}
