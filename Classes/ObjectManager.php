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
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Exception\InvalidConfigurationException;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use LogicException;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function class_exists;
use function func_num_args;
use function interface_exists;
use function sprintf;

/**
 * Specialized Object Manager
 */
class ObjectManager implements ObjectManagerInterface, SingletonInterface
{
    protected ?ConfigurationProviderInterface $configurationProvider = null;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: GeneralUtility::makeInstance(ContainerInterface::class);
    }

    public function get(string $class): object
    {
        if (func_num_args() > 1) {
            throw new LogicException('Passing additional arguments to `get()` is not supported anymore');
        }

        return $this->container->get($class);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->get(ResponseFactoryInterface::class);
    }

    public function getDataProvider(RestRequestInterface $request): DataProviderInterface
    {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getImplementationFromResourceConfiguration($resourceType, 'DataProvider');
        if ($dataProvider) {
            return $dataProvider;
        }

        [, $extension,] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check for a specific builtin Data Provider
        $specialDataProvider = sprintf('Cundd\\Rest\\DataProvider\\%sDataProvider', $extension);
        if (class_exists($specialDataProvider)) {
            return $this->get($specialDataProvider);
        } else {
            return $this->get(DataProviderInterface::class);
        }
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->get(RequestFactoryInterface::class);
    }

    public function getAuthenticationProvider(RestRequestInterface $request): AuthenticationProviderInterface
    {
        $resourceType = $request->getResourceType();
        [$vendor, $extension,] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check if an extension provides a Authentication Provider
        $authenticationProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AuthenticationProvider';

        // Use the found Authentication Provider
        if (class_exists($authenticationProviderClass)) {
            return $this->get($authenticationProviderClass);
        }

        // Use the Authentication Providers defined in TypoScript
        $providerInstances = [];
        $configuredProviders = $this->getConfigurationProvider()->getSetting('authenticationProvider') ?? [];
        ksort($configuredProviders);
        foreach ($configuredProviders as $providerClass) {
            if (class_exists($providerClass)) {
                $providerInstances[] = $this->get(ltrim($providerClass, '\\'));
            }
        }

        return new AuthenticationProviderCollection($providerInstances);
    }

    public function getAccessController(RestRequestInterface $request): AccessControllerInterface
    {
        $resourceType = $request->getResourceType();
        [$vendor, $extension,] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check if an extension provides an Authentication Provider
        $accessControllerClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AccessController';
        if (class_exists($accessControllerClass)) {
            return $this->get($accessControllerClass);
        } else {
            // Use the configuration based Authentication Provider
            return $this->get(ConfigurationBasedAccessController::class);
        }
    }

    public function getHandler(RestRequestInterface $request): HandlerInterface
    {
        $resourceType = $request->getResourceType();
        $handler = $this->getImplementationFromResourceConfiguration($resourceType, 'Handler');
        if ($handler) {
            return $handler;
        }

        [, $extension,] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check for a specific builtin Handler
        $specialHandler = 'Cundd\\Rest\\Handler\\' . $extension . 'Handler';
        if (class_exists($specialHandler)) {
            return $this->has($specialHandler)
                ? $this->get($specialHandler)
                : GeneralUtility::makeInstance($specialHandler);
        } else {
            return $this->get(CrudHandler::class);
        }
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

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }

    /**
     * @param ResourceType $resourceType
     * @param string       $type
     * @return mixed
     */
    private function getImplementationFromResourceConfiguration(ResourceType $resourceType, string $type)
    {
        $resourceConfiguration = $this->getConfigurationProvider()->getResourceConfiguration($resourceType);
        if (!$resourceConfiguration) {
            // This case should not occur in reality, since at least the `all` Resource should have been configured
            throw new InvalidConfigurationException(
                sprintf('Resource "%s" is not configured', (string)$resourceType)
            );
        }

        $getter = 'get' . ucfirst($type) . 'Class';
        $implementation = $resourceConfiguration->$getter();
        if (!$implementation) {
            return null;
        }

        if (!class_exists($implementation) && !interface_exists($implementation)) {
            throw new InvalidConfigurationException(
                sprintf('Configured %s "%s" does not exist', $type, $implementation)
            );
        }

        $implementation = ltrim($implementation, '\\');

        return $this->get($implementation);
    }
}
