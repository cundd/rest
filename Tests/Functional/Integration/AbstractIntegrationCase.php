<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\Logger;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\Functional\Fixtures\TestResponseFactory;
use Cundd\Rest\Tests\RequestBuilderTrait;
use InvalidArgumentException;
use Nimut\TestingFramework\Http\Response as NimutResponse;
use PHPUnit\Util\PHP\DefaultPhpProcess;
use Psr\Http\Message\ResponseInterface;
use Text_Template;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface as Typo3ObjectManagerInterface;

use function is_array;
use function is_string;
use function json_decode;
use function putenv;
use function var_export;

class AbstractIntegrationCase extends AbstractCase
{
    use RequestBuilderTrait;

    protected $testExtensionsToLoad = ['typo3conf/ext/rest'];

    /**
     * @var DispatcherInterface
     */
    private $dispatcher;

    public function setUp(): void
    {
        // Set TEST_MODE to true
        putenv('TEST_MODE=true');
        parent::setUp();

        // Unset the Object Manager property to prevent serialization errors in case a failure occurs
        unset($this->objectManager);
    }

    protected function configurePath(
        Typo3ObjectManagerInterface $objectManager,
        string $path,
        array $pathConfiguration
    ) {
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
     * @param Typo3ObjectManagerInterface $objectManager
     * @param RestRequestInterface        $request
     * @return ResponseInterface
     */
    public function dispatch(
        Typo3ObjectManagerInterface $objectManager,
        RestRequestInterface $request
    ): ResponseInterface {
        $objectManager->get(RequestFactoryInterface::class)->registerCurrentRequest($request);

        $dispatcher = new Dispatcher(
            $objectManager->get(ObjectManagerInterface::class),
            $objectManager->get(RequestFactoryInterface::class),
            $objectManager->get(ResponseFactoryInterface::class),
            new Logger(new StreamLogger()),
            null
        );

        return $dispatcher->dispatch($request);
    }

    /**
     * Build a request and dispatch it using the REST Dispatcher
     *
     * @param Typo3ObjectManagerInterface $objectManager
     * @param string                      $path
     * @param string                      $method
     * @param string|array|null           $body
     * @param array                       $headers
     * @param null                        $basicAuth Ignored
     * @return ResponseInterface
     * @see dispatch()
     */
    public function buildRequestAndDispatch(
        Typo3ObjectManagerInterface $objectManager,
        string $path,
        string $method = 'GET',
        $body = null,
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

    /**
     * Dispatch a Frontend Request using the Nimut testing framework
     *
     * Use this method to preform a full Functional Test against TYPO3's frontend.
     *
     * Limitations:
     *  - POST requests are not supported
     *  - Headers are not supported
     *
     * @param string $path
     * @param int    $backendUserId
     * @param int    $workspaceId
     * @param bool   $failOnFailure
     * @param int    $frontendUserId
     * @return ResponseInterface
     */
    protected function fetchFrontendResponse(
        string $path,
        $backendUserId = 0,
        $workspaceId = 0,
        $failOnFailure = true,
        $frontendUserId = 0
    ): ResponseInterface {
        $additionalParameter = '';

        if (!empty($frontendUserId)) {
            $additionalParameter .= '&frontendUserId=' . (int)$frontendUserId;
        }
        if (!empty($backendUserId)) {
            $additionalParameter .= '&backendUserId=' . (int)$backendUserId;
        }
        if (!empty($workspaceId)) {
            $additionalParameter .= '&workspaceId=' . (int)$workspaceId;
        }

        $arguments = [
            'documentRoot'         => $this->getInstancePath(),
            'requestUrl'           => 'http://localhost' . $path . $additionalParameter,
            'HTTP_ACCEPT_LANGUAGE' => 'de-DE',
        ];

        $template = new Text_Template('ntf://Frontend/Request.tpl');
        $template->setVar(
            [
                'arguments'    => var_export($arguments, true),
                'originalRoot' => ORIGINAL_ROOT,
                'ntfRoot'      => __DIR__ . '/../../../vendor/nimut/testing-framework/',
            ]
        );

        $php = DefaultPhpProcess::factory();
        $response = $php->runJob($template->render());
        $result = json_decode($response['stdout'], true);

        if ($result === null) {
            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
        }

        if ($failOnFailure && $result['status'] === NimutResponse::STATUS_Failure) {
            $this->fail('Frontend Response has failure:' . LF . $result['error']);
        }

        return TestResponseFactory::fromResponse(
            new NimutResponse($result['status'], $result['content'], $result['error'])
        );
    }

    /**
     * @param ResponseInterface|NimutResponse|string $response
     * @return string
     */
    protected function getErrorDescription(ResponseInterface $response): string
    {
        if ($response instanceof ResponseInterface) {
            $body = (string)(clone $response->getBody());
        } elseif ($response instanceof NimutResponse) {
            $body = $response->getContent();
        } else {
            $body = (string)$response;
        }
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr($body, 0, (int)getenv('ERROR_BODY_LENGTH') ?: 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error with response content: %s',
            $bodyPart
        );
    }

    /**
     * @param ResponseInterface|NimutResponse|string|null|array $response
     * @return mixed
     */
    protected function getParsedBody($response)
    {
        if ($response instanceof ResponseInterface) {
            return $this->getParsedBody((string)$response->getBody());
        } elseif ($response instanceof NimutResponse) {
            return $this->getParsedBody($response->getContent());
        } elseif (is_array($response)) {
            return $response;
        } elseif (is_string($response)) {
            return json_decode($response, true);
        } else {
            throw new InvalidArgumentException();
        }
    }
}
