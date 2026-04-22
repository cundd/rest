<?php

declare(strict_types=1);

namespace Cundd\Rest\Documentation;

use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Documentation\Handler\DescriptiveRouter;
use Cundd\Rest\Documentation\Handler\DummyRequest;
use Cundd\Rest\Exception\InvalidConfigurationException;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Router\RouteInterface;
use Exception;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * @phpstan-type HandlerInformation array{
 *     handler?:HandlerInterface,
 *     configuration:ResourceConfiguration,
 *     handlerClass:string|class-string<HandlerInterface>,
 *     routes:RouteInterface[][],
 *     errorMessage?:string
 * }
 */
final readonly class HandlerDescriptor
{
    /**
     * Handler Descriptor constructor
     */
    public function __construct(
        private ObjectManagerInterface $objectManager,
        private ConfigurationProviderFactoryInterface $configurationProviderFactory,
    ) {
    }

    /**
     * Return information about all registered Handlers and their configured Routes
     *
     * @return array<string, HandlerInformation>
     */
    public function getInformation(Site $site): array
    {
        $handlerConfigurations = $this->configurationProviderFactory
            ->buildFromSite($site)
            ->getConfiguredResources();

        $information = [];
        foreach ($handlerConfigurations as $path => $handlerConfiguration) {
            $information[$path] = $this->fetchInformationForHandler($handlerConfiguration);
        }

        ksort($information);

        return $information;
    }

    /**
     * @return HandlerInformation
     */
    private function fetchInformationForHandler(ResourceConfiguration $configuration): array
    {
        /** @var class-string<HandlerInterface> $className */
        $className = $configuration->getHandlerClass();
        if (!$className) {
            $className = CrudHandler::class;
        }
        if (!class_exists($className)) {
            $error = $this->buildException(
                'Handler class "%s" does not seem to exist',
                $className
            );

            return $this->buildError($error, $className, $configuration);
        }
        if ('\\' === $className[0]) {
            $className = substr($className, 1);
        }

        assert(is_a($className, HandlerInterface::class, true));
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
            'handlerClass'  => $className,
            'configuration' => $configuration,
            'routes'        => $this->filterEmptyMethods($router),
        ];
    }

    /**
     * @return HandlerInformation
     */
    private function buildError(
        Exception $exception,
        string $handlerClass,
        ResourceConfiguration $configuration,
    ): array {
        return [
            'handlerClass'  => $handlerClass,
            'configuration' => $configuration,
            'errorMessage'  => $exception->getMessage(),
            'error'         => $exception,
            'trace'         => $exception->getTraceAsString(),
            'routes'        => [],
        ];
    }

    private function buildException(
        string $message,
        mixed ...$arguments,
    ): InvalidConfigurationException {
        return new InvalidConfigurationException(vsprintf($message, $arguments));
    }

    /**
     * @return RouteInterface[][]
     */
    private function filterEmptyMethods(DescriptiveRouter $router): array
    {
        return array_filter($router->getRegisteredRoutes());
    }
}
