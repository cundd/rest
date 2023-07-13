<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\Logger;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

use function is_array;
use function json_decode;
use function putenv;
use function sprintf;
use function substr;

class AbstractIntegrationCase extends AbstractCase
{
    use RequestBuilderTrait;
    use FrontendRequestTrait;

    protected array $testExtensionsToLoad = ['typo3conf/ext/rest'];

    private DispatcherInterface $dispatcher;

    public function setUp(): void
    {
        // Set TEST_MODE to true
        putenv('TEST_MODE=true');
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->setUpFrontendRootPage(1, ['EXT:rest/ext_typoscript_setup.txt']);
    }

    protected function configurePath(
        ContainerInterface $objectManager,
        string $path,
        array $pathConfiguration
    ): void {
        /** @var TypoScriptConfigurationProvider $configurationProvider */
        $configurationProvider = $objectManager->get(ConfigurationProviderInterface::class);
        $configuration = $configurationProvider->getSettings();
        $configuration['paths'][$path] = $pathConfiguration;
        $configurationProvider->setSettings($configuration);
    }

    /**
     * Dispatch the given Request using the REST Dispatcher
     *
     * Use this method to preform an Integration Test against the REST extension's dispatching mechanism.
     *
     * Limitations:
     *  - This will bypass TYPO3's routing
     *
     * @param ContainerInterface   $container
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(
        ContainerInterface $container,
        RestRequestInterface $request
    ): ResponseInterface {
        $dispatcher = new Dispatcher(
            $container->get(ObjectManager::class),
            $container->get(RequestFactoryInterface::class),
            $container->get(ResponseFactoryInterface::class),
            new Logger(new StreamLogger()),
            $container->get(RouterInterface::class),
            null
        );

        return $dispatcher->dispatch($request);
    }

    /**
     * Build a request and dispatch it using the REST Dispatcher
     *
     * @param ContainerInterface $objectManager
     * @param string             $path
     * @param string             $method
     * @param array|string|null  $body
     * @param array              $headers
     * @param null               $basicAuth Ignored
     * @return ResponseInterface
     * @see dispatch()
     */
    public function buildRequestAndDispatch(
        ContainerInterface $objectManager,
        string $path,
        string $method = 'GET',
        array|string|null $body = null,
        array $headers = [],
        /** @noinspection PhpUnusedParameterInspection */
        $basicAuth = null
    ): ResponseInterface {
        $uri = 'http://localhost:8888/' . ltrim($path, '/');
        $request = $this->buildTestRequest(
            $uri,
            $method,
            [],
            $headers,
            $body,
            is_array($body) ? $body : null
        );

        return $this->dispatch($objectManager, $request);
    }

    protected function getErrorDescription(ResponseInterface $response): string
    {
        $body = (string)(clone $response->getBody());
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr($body, 0, (int)getenv('ERROR_BODY_LENGTH') ?: 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error with response content: %s',
            $bodyPart
        );
    }

    protected function getParsedBody(ResponseInterface|string $response): mixed
    {
        if ($response instanceof ResponseInterface) {
            return $this->getParsedBody((string)$response->getBody());
        } else {
            return json_decode($response, true);
        }
    }
}
