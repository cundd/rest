<?php

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
use Cundd\Rest\Tests\Functional\Database\Factory;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\Response;

class AbstractIntegrationCase extends AbstractCase
{
    use RequestBuilderTrait;

    /**
     * @var DispatcherInterface
     */
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->configureConfigurationProvider();
//        $this->configureDatabaseAdapter();

        $this->dispatcher = new Dispatcher(
            $this->objectManager->get(ObjectManagerInterface::class),
            $this->objectManager->get(RequestFactoryInterface::class),
            $this->objectManager->get(ResponseFactoryInterface::class),
            new Logger(new StreamLogger())
        );
    }

    /**
     * Dispatch the given Request
     *
     * @param RestRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(RestRequestInterface $request)
    {
        $this->objectManager->get(RequestFactoryInterface::class)->registerCurrentRequest($request);

        return $this->dispatcher->dispatch($request, new Response());
    }

    /**
     * Build a request and dispatch it
     *
     * @param string $path
     * @param string $method
     * @param array  $body
     * @param array  $headers
     * @param null   $basicAuth Ignored
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        $uri = 'http://localhost:8888/' . ltrim($path, '/');
        $request = $this->buildTestRequest($uri, $method, [], $headers, $body, $body);

        return $this->dispatch($request);
    }

    protected function getErrorDescription(ResponseInterface $response)
    {
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr((string)$response->getBody(), 0, 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error with response content: %s',
            $bodyPart
        );
    }

    private function configureConfigurationProvider()
    {
        /** @var TypoScriptConfigurationProvider $configurationProvider */
        $configurationProvider = $this->objectManager->get(ConfigurationProviderInterface::class);
        $configurationProvider->setSettings(
            [
                "paths"            => [
                    "all" => [
                        "path"  => "all",
                        "read"  => "deny",
                        "write" => "deny",
                    ],

                    "document" => [
                        "path"  => "Document",
                        "read"  => "deny",
                        "write" => "deny",
                    ],

                    "auth" => [
                        "path"  => "auth",
                        "read"  => "allow",
                        "write" => "allow",
                    ],
                ],

                # Define words that should not be converted to singular
                "singularToPlural" => [
                    "news"        => "news",
                    "equipment"   => "equipment",
                    "information" => "information",
                    "rice"        => "rice",
                    "money"       => "money",
                    "species"     => "species",
                    "series"      => "series",
                    "fish"        => "fish",
                    "sheep"       => "sheep",
                    "press"       => "press",
                    "sms"         => "sms",
                ],
            ]
        );
    }
}
