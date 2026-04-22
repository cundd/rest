<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\DispatcherFactory;
use Cundd\Rest\Dispatcher\ResponseHeaderUpdaterInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\Logger;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

use function json_decode;
use function putenv;
use function sprintf;
use function substr;

class AbstractIntegrationCase extends AbstractCase
{
    use RequestBuilderTrait;
    use FrontendRequestTrait;

    // protected array $testExtensionsToLoad = ['typo3conf/ext/rest'];

    public function setUp(): void
    {
        // Set TEST_MODE to true
        putenv('TEST_MODE=true');
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->setUpFrontendRootPage(1000);
    }

    /**
     * @param array<string,mixed> $pathConfiguration
     */
    protected function configurePath(
        ContainerInterface $objectManager,
        string $path,
        array $pathConfiguration,
    ): void {
        $this->mergeSiteConfiguration(
            'test-site',
            [
                'settings' => [
                    'rest' => [
                        'settings' => [
                            'paths' => [
                                $path => $pathConfiguration,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Dispatch the given Request using the REST Dispatcher
     *
     * Use this method to preform an Integration Test against the REST
     * extension's dispatching mechanism.
     *
     * Limitations:
     *  - This will bypass TYPO3's routing
     */
    public function dispatch(
        ContainerInterface $container,
        RestRequestInterface $request,
    ): ResponseInterface {
        // $dispatcher = new Dispatcher(
        //     $container->get(ObjectManagerInterface::class),
        //     $container->get(RequestFactoryInterface::class),
        //     $container->get(ResponseFactoryInterface::class),
        //     new Logger(new StreamLogger()),
        //     $container->get(RouterInterface::class),
        //     $container->get(ResponseHeaderUpdaterInterface::class),
        //     $container->get(EventDispatcherInterface::class),
        // );

        /** @var Dispatcher $dispatcher */
        $dispatcher = $container->get(DispatcherFactory::class)->build();

        return $dispatcher->dispatch($request);
    }

    /**
     * Build a request and dispatch it using the REST Dispatcher
     *
     * @param array<mixed,mixed>|string|null $body
     * @param null                           $basicAuth Ignored
     * @param array<mixed,mixed>             $headers
     *
     * @see dispatch()
     */
    public function buildRequestAndDispatch(
        ContainerInterface $container,
        string $path,
        string $method = 'GET',
        array|string|null $body = null,
        array $headers = [],
        /** @noinspection PhpUnusedParameterInspection */
        $basicAuth = null,
        ?Site $site = null,
    ): ResponseInterface {
        $request = $this->buildTestRequestWithSite(
            $path,
            $method,
            $body,
            $headers,
            $site
        );

        return $this->dispatch($container, $request);
    }

    protected function getErrorDescription(ResponseInterface $response): string
    {
        $body = (string) (clone $response->getBody());
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr($body, 0, (int) getenv('ERROR_BODY_LENGTH') ?: 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error with response content: %s',
            $bodyPart
        );
    }

    protected function getParsedBody(ResponseInterface|string $response): mixed
    {
        if ($response instanceof ResponseInterface) {
            return $this->getParsedBody((string) $response->getBody());
        }

        return json_decode($response, true);
    }
}
