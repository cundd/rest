<?php


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
    public function getInformation()
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
    private function fetchInformationForHandler(ResourceConfiguration $configuration)
    {
        $className = $configuration->getHandlerClass();
        if (!$className) {
            $className = CrudHandler::class;
        }
        if (!class_exists($className)) {
            return $this->buildError($this->buildException('Handler class "%s" does not seem to exist', $className));
        }
        if ($className[0] === '\\') {
            $className = substr($className, 1);
        }

        try {
            $handler = $this->objectManager->get($className);
        } catch (\Exception $exception) {
            return $this->buildError($exception);
        }
        if (!($handler instanceof HandlerInterface)) {
            return $this->buildError(
                $this->buildException(
                    'Registered handler class "%s" does not implement "%s"',
                    $className,
                    HandlerInterface::class
                )
            );
        }

        $router = new DescriptiveRouter();
        $request = new DummyRequest($configuration->getResourceType());
        try {
            $handler->configureRoutes($router, $request);
        } catch (\Exception $exception) {
            return $this->buildError($exception);
        }

        return [
            'handler'       => $handler,
            'configuration' => $configuration,
            'routes'        => $this->filterEmptyMethods($router),
        ];
    }

    private function buildError(\Exception $exception)
    {
        return [
            'errorMessage' => $exception->getMessage(),
            'error'        => $exception,
            'trace'        => $exception->getTraceAsString(),
        ];
    }

    private function buildException($message, ...$arguments)
    {
        return new InvalidConfigurationException(vsprintf($message, $arguments));
    }

    /**
     * @param $router
     * @return RouteInterface[][]
     */
    private function filterEmptyMethods(DescriptiveRouter $router)
    {
        return array_filter($router->getRegisteredRoutes());
    }
}
