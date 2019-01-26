<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\DispatcherInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\Logger;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
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
    public function request(
        $path,
        $method = 'GET',
        $body = null,
        array $headers = [],
        /** @noinspection PhpUnusedParameterInspection */
        $basicAuth = null
    ) {
        $uri = 'http://localhost:8888/' . ltrim($path, '/');
        $request = $this->buildTestRequest($uri, $method, [], $headers, $body, $body);

        return $this->dispatch($request);
    }

    protected function getErrorDescription(ResponseInterface $response)
    {
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr((string)$response->getBody(), 0, getenv('ERROR_BODY_LENGTH') ?: 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error with response content: %s',
            $bodyPart
        );
    }
}
