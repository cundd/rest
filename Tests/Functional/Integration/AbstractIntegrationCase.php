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
//        $this->setUpBackendUser(1);
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
     * @param ContainerInterface   $objectManager
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(
        ContainerInterface $objectManager,
        RestRequestInterface $request
    ): ResponseInterface {
        $dispatcher = new Dispatcher(
            $objectManager->get(ObjectManager::class),
            $objectManager->get(RequestFactoryInterface::class),
            $objectManager->get(ResponseFactoryInterface::class),
            new Logger(new StreamLogger()),
            $objectManager->get(RouterInterface::class),
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

//    /**
//     * Dispatch a Frontend Request using the Nimut testing framework
//     *
//     * Use this method to preform a full Functional Test against TYPO3's frontend.
//     *
//     * Limitations:
//     *  - POST requests are not supported
//     *  - Headers are not supported
//     *
//     * @param string   $path
//     * @param int      $backendUserId
//     * @param int      $workspaceId
//     * @param int      $frontendUserId
//     * @param int|null $pageId
//     * @return ResponseInterface
//     */
//    protected function fetchFrontendResponse(
//        string $path,
//        int $backendUserId = 0,
//        int $workspaceId = 0,
//        int $frontendUserId = 0,
//        ?int $pageId = null
//    ): ResponseInterface {
//        $additionalParameter = '';
//
//        if (!empty($frontendUserId)) {
//            $additionalParameter .= '&frontendUserId=' . (int)$frontendUserId;
//        }
//        if (!empty($backendUserId)) {
//            $additionalParameter .= '&backendUserId=' . (int)$backendUserId;
//        }
//        if (!empty($workspaceId)) {
//            $additionalParameter .= '&workspaceId=' . (int)$workspaceId;
//        }
//
//        $internalRequest = new InternalRequest('http://localhost' . $path . $additionalParameter);
//        if (null !== $pageId) {
//            return $this->executeFrontendSubRequest($internalRequest->withPageId($pageId));
//        } else {
//            return $this->executeFrontendSubRequest($internalRequest);
//        }
////
////        $arguments = [
////            'documentRoot'         => $this->getInstancePath(),
////            'requestUrl'           => 'http://localhost' . $path . $additionalParameter,
////            'HTTP_ACCEPT_LANGUAGE' => 'de-DE',
////        ];
////
////        $template = new Text_Template('ntf://Frontend/Request.tpl');
////        $template->setVar(
////            [
////                'arguments'    => var_export($arguments, true),
////                'originalRoot' => ORIGINAL_ROOT,
////                'ntfRoot'      => __DIR__ . '/../../../vendor/nimut/testing-framework/',
////            ]
////        );
////
////        $php = DefaultPhpProcess::factory();
////        $response = $php->runJob($template->render());
////        $result = json_decode($response['stdout'], true);
////
////        if ($result === null) {
////            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
////        }
////
////        if ($failOnFailure && $result['status'] === NimutResponse::STATUS_Failure) {
////            $this->fail('Frontend Response has failure:' . LF . $result['error']);
////        }
////
////        return TestResponseFactory::fromResponse(
////            new NimutResponse($result['status'], $result['content'], $result['error'])
////        );
//    }

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
