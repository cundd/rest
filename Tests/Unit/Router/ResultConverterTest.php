<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Router;

use Closure;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Exception\NotFoundException;
use Cundd\Rest\Router\ResultConverter;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;

final class ResultConverterTest extends TestCase
{
    use ProphecyTrait;
    use RequestBuilderTrait;

    protected ResultConverter $fixture;

    protected ResponseFactoryInterface $responseFactory;

    private Closure $exceptionHandler;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('TEST_MODE=yes');
        $this->responseFactory = new ResponseFactory();
        $this->exceptionHandler = function () {
        };
    }

    protected function tearDown(): void
    {
        unset($this->responseFactory);
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function dispatchTest(): void
    {
        /* @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter('some result'),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('{"message":"some result"}', (string) $result->getBody());
    }

    #[Test]
    public function dispatchNotFoundTest(): void
    {
        /* @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter(new NotFoundException()),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(404, $result->getStatusCode());
        $this->assertSame('{"error":"Not Found"}', (string) $result->getBody());
    }

    #[Test]
    public function dispatchArrayTest(): void
    {
        /* @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter(['some' => 'data', 'key' => 'hello']),
            $this->responseFactory,
            function () {
            }
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('{"some":"data","key":"hello"}', (string) $result->getBody());
    }

    #[Test]
    public function dispatchWillForwardResultToResponseFactoryTest(): void
    {
        $request = $this->buildTestRequest('');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $responseFactoryProphecy->createSuccessResponse(
            'some result',
            200,
            $request
        );
        $methodProphecy->shouldBeCalled();

        $this->responseFactory = $responseFactoryProphecy->reveal();

        /* @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter('some result'),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $this->fixture->dispatch($request);
    }

    #[Test]
    public function dispatchWillPassthroughResponseTest(): void
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $this->fixture = new ResultConverter(
            $this->buildRouter($response),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));

        $this->assertSame($response, $result);
    }

    #[Test]
    public function dispatchWillCaptureExceptionsTest(): void
    {
        $this->fixture = new ResultConverter(
            $this->buildRouter(
                function () {
                    throw new Exception('An exception', 1483531241);
                }
            ),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(501, $result->getStatusCode());
        $this->assertSame(
            '{"error":"Sorry! Something is wrong. Exception code #1483531241"}',
            (string) $result->getBody()
        );
    }

    #[Test]
    public function dispatchWillConvertExceptionsTest(): void
    {
        $this->fixture = new ResultConverter(
            $this->buildRouter(new Exception('An exception', 1483531241)),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(501, $result->getStatusCode());
        $this->assertSame(
            '{"error":"Sorry! Something is wrong. Exception code #1483531241"}',
            (string) $result->getBody()
        );
    }

    private function buildRouter(mixed $response): RouterInterface
    {
        $router = $this->prophesize(RouterInterface::class);

        /** @var RestRequestInterface $request */
        $request = Argument::any();
        if (is_callable($response)) {
            $router->dispatch($request)->will($response);
        } else {
            $router->dispatch($request)->willReturn($response);
        }

        return $router->reveal();
    }

    /**
     * @return string[]
     */
    public function __sleep(): array
    {
        $properties = get_object_vars($this);

        // Do not try to serialize the `exceptionHandler` callback (only happens in case of an error)
        unset($properties['exceptionHandler']);

        return array_keys($properties);
    }
}
