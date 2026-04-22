<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Access\AccessControllerInterface;
use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Cache\CacheFactory;
use Cundd\Rest\Cache\CacheInterface;
use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Exception\InvalidConfigurationException;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\ResourceType;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
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
    protected ContainerInterface $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container ?: GeneralUtility::makeInstance(ContainerInterface::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function get(string $class): object
    {
        if (func_num_args() > 1) {
            throw new LogicException(
                'Passing additional arguments to `get()` is not supported anymore'
            );
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
        $dataProvider = $this->getImplementationFromResourceConfiguration(
            $request,
            $resourceType,
            'DataProvider'
        );

        if ($dataProvider) {
            assert($dataProvider instanceof DataProviderInterface);

            return $dataProvider;
        }

        [, $extension] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check for a specific builtin Data Provider
        $specialDataProvider = sprintf('Cundd\\Rest\\DataProvider\\%sDataProvider', $extension);
        if (class_exists($specialDataProvider)) {
            $dataProvider = $this->get($specialDataProvider);
            assert($dataProvider instanceof DataProviderInterface);

            return $dataProvider;
        }

        return $this->get(DataProviderInterface::class);
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->get(RequestFactoryInterface::class);
    }

    public function getAuthenticationProvider(
        RestRequestInterface $request,
    ): AuthenticationProviderInterface {
        $resourceType = $request->getResourceType();
        [$vendor, $extension] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check if an extension provides a Authentication Provider
        $authenticationProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AuthenticationProvider';

        // Use the found Authentication Provider
        if (class_exists($authenticationProviderClass)) {
            $authenticationProvider = $this->get($authenticationProviderClass);
            assert($authenticationProvider instanceof AuthenticationProviderInterface);

            return $authenticationProvider;
        }

        // Use the Authentication Providers defined in TypoScript
        $providerInstances = [];
        $configuredProviders = $this->getConfigurationProvider($request)
            ->getSetting('authenticationProvider') ?? [];

        ksort($configuredProviders);
        foreach ($configuredProviders as $providerClass) {
            if (class_exists($providerClass)) {
                $providerInstance = $this->get(ltrim($providerClass, '\\'));
                assert($providerInstance instanceof AuthenticationProviderInterface);
                $providerInstances[] = $providerInstance;
            }
        }

        return new AuthenticationProviderCollection($providerInstances);
    }

    public function getAccessController(RestRequestInterface $request): AccessControllerInterface
    {
        $resourceType = $request->getResourceType();
        [$vendor, $extension] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check if an extension provides an Authentication Provider
        $accessControllerClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AccessController';
        if (class_exists($accessControllerClass)) {
            $accessController = $this->get($accessControllerClass);
            assert($accessController instanceof AccessControllerInterface);

            return $accessController;
        }

        // Use the configuration based Authentication Provider
        return $this->get(ConfigurationBasedAccessController::class);
    }

    public function getHandler(RestRequestInterface $request): HandlerInterface
    {
        $resourceType = $request->getResourceType();
        $handler = $this->getImplementationFromResourceConfiguration(
            $request,
            $resourceType,
            'Handler'
        );

        if ($handler) {
            assert($handler instanceof HandlerInterface);

            return $handler;
        }

        [, $extension] = Utility::getClassNamePartsForResourceType($resourceType);

        // Check for a specific builtin Handler
        $specialHandlerClass = 'Cundd\\Rest\\Handler\\' . $extension . 'Handler';
        if (class_exists($specialHandlerClass)) {
            $handler = $this->has($specialHandlerClass)
                ? $this->get($specialHandlerClass)
                : GeneralUtility::makeInstance($specialHandlerClass);

            assert($handler instanceof HandlerInterface);

            return $handler;
        }

        return $this->get(CrudHandler::class);
    }

    public function getCache(
        RestRequestInterface $request,
        ResourceType $resourceType,
    ): CacheInterface {
        /** @var CacheFactory $cacheFactory */
        $cacheFactory = $this->get(CacheFactory::class);

        return $cacheFactory->buildCache(
            $resourceType,
            $this->getConfigurationProvider($request),
            $this
        );
    }

    public function getConfigurationProvider(
        ServerRequestInterface $request,
    ): ConfigurationProviderInterface {
        $configurationProviderFactory = $this->get(ConfigurationProviderFactoryInterface::class);

        return $configurationProviderFactory->build($request);
    }

    /**
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->container->$name(...$arguments);
    }

    private function getImplementationFromResourceConfiguration(
        RestRequestInterface $request,
        ResourceType $resourceType,
        string $type,
    ): ?object {
        $resourceConfiguration = $this->getConfigurationProvider($request)
            ->getResourceConfiguration($resourceType);
        if (!$resourceConfiguration) {
            // This case should not occur in reality, since at least the
            // `all`-`Resource` should have been configured
            throw new InvalidConfigurationException(
                sprintf('Resource "%s" is not configured', (string) $resourceType)
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
