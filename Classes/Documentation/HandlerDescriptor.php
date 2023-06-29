<?php

declare(strict_types=1);

namespace Cundd\Rest\Documentation;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Documentation\Handler\DescriptiveRouter;
use Cundd\Rest\Documentation\Handler\DummyRequest;
use Cundd\Rest\Exception\InvalidConfigurationException;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Router\RouteInterface;
use Exception;

class HandlerDescriptor
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConfigurationProviderInterface
     */
    private $configurationProvider;

    /**
     * Handler Descriptor constructor
     *
     * @param ObjectManagerInterface         $objectManager
     * @param ConfigurationProviderInterface $configurationProvider
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigurationProviderInterface $configurationProvider
    ) {
        $this->objectManager = $objectManager;
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * Return information about all registered Handlers and their configured Routes
     *
     * @return array
     */
    public function getInformation(): array
    {
        $handlerConfigurations = $this->configurationProvider->getConfiguredResources();

        $information = [];
        foreach ($handlerConfigurations as $path => $handlerConfiguration) {
            $information[$path] = $this->fetchInformationForHandler($handlerConfiguration);
        }

        return $information;
    }

    /**
     * @param ResourceConfiguration $configuration
     * @return array
     */
    private function fetchInformationForHandler(ResourceConfiguration $configuration): array
    {
        $className = $configuration->getHandlerClass();
        if (!$className) {
            $className = CrudHandler::class;
        }
        if (!class_exists($className)) {
            $error = $this->buildException('Handler class "%s" does not seem to exist', $className);

            return $this->buildError($error, $className, $configuration);
        }
        if ($className[0] === '\\') {
            $className = substr($className, 1);
        }

        try {
            $handler = $this->objectManager->get($className);
        } catch (Exception $exception) {
            return $this->buildError($exception, $className, $configuration);
        }
        if (!($handler instanceof HandlerInterface)) {
            $error = $this->buildException(
                'Registered handler class "%s" does not implement "%s"',
                $className,
                HandlerInterface::class
            );

            return $this->buildError($error, $className, $configuration);
        }

        $router = new DescriptiveRouter();
        $request = new DummyRequest($configuration->getResourceType());
        try {
            $handler->configureRoutes($router, $request);
        } catch (Exception $exception) {
            return $this->buildError($exception, $className, $configuration);
        }

        return [
            'handler'       => $handler,
            'configuration' => $configuration,
            'routes'        => $this->filterEmptyMethods($router),
        ];
    }

    private function buildError(Exception $exception, string $handlerClass, $configuration): array
    {
        return [
            'handlerClass'  => $handlerClass,
            'configuration' => $configuration,
            'errorMessage'  => $exception->getMessage(),
            'error'         => $exception,
            'trace'         => $exception->getTraceAsString(),
        ];
    }

    private function buildException(string $message, ...$arguments): InvalidConfigurationException
    {
        return new InvalidConfigurationException(vsprintf($message, $arguments));
    }

    /**
     * @param $router
     * @return RouteInterface[][]
     */
    private function filterEmptyMethods(DescriptiveRouter $router): array
    {
        return array_filter($router->getRegisteredRoutes());
    }
}
