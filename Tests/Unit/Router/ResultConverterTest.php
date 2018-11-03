<?php

namespace Cundd\Rest\Tests\Unit\Router;


use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Exception\NotFoundException;
use Cundd\Rest\Router\ResultConverter;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;


class ResultConverterTest extends \PHPUnit\Framework\TestCase
{
    use RequestBuilderTrait;

    /**
     * @var ResultConverter
     */
    protected $fixture;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var callable
     */
    private $exceptionHandler;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->responseFactory = new ResponseFactory();
        $this->exceptionHandler = function () {
        };
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->responseFactory);
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function dispatchTest()
    {
        /** @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter('some result'),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('{"message":"some result"}', (string)$result->getBody());
    }

    /**
     * @test
     */
    public function dispatchNotFoundTest()
    {
        /** @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter(new NotFoundException()),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(404, $result->getStatusCode());
        $this->assertSame('{"error":"Not Found"}', (string)$result->getBody());
    }

    /**
     * @test
     */
    public function dispatchArrayTest()
    {
        /** @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter(['some' => 'data', 'key' => 'hello']),
            $this->responseFactory,
            function () {
            }
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('{"some":"data","key":"hello"}', (string)$result->getBody());
    }

    /**
     * @test
     */
    public function dispatchWillForwardResultToResponseFactoryTest()
    {
        $request = $this->buildTestRequest('');

        /** @var ResponseFactoryInterface|ObjectProphecy $responseFactoryProphecy */
        $responseFactoryProphecy = $this->prophesize(ResponseFactory::class);
        $responseFactoryProphecy->createSuccessResponse('some result', 200, $request)->shouldBeCalled();

        $this->responseFactory = $responseFactoryProphecy->reveal();

        /** @var ResponseInterface|ObjectProphecy $response */
        $this->fixture = new ResultConverter(
            $this->buildRouter('some result'),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $this->fixture->dispatch($request);
    }

    /**
     * @test
     */
    public function dispatchWillPassthroughResponseTest()
    {
        /** @var ResponseInterface|ObjectProphecy $response */
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $this->fixture = new ResultConverter(
            $this->buildRouter($response),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));

        $this->assertSame($response, $result);
    }

    /**
     * @test
     */
    public function dispatchWillCaptureExceptionsTest()
    {
        $this->fixture = new ResultConverter(
            $this->buildRouter(
                function () {
                    throw new \Exception('An exception', 1483531241);
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
            (string)$result->getBody()
        );
    }

    /**
     * @test
     */
    public function dispatchWillConvertExceptionsTest()
    {
        $this->fixture = new ResultConverter(
            $this->buildRouter(new \Exception('An exception', 1483531241)),
            $this->responseFactory,
            $this->exceptionHandler
        );

        $result = $this->fixture->dispatch($this->buildTestRequest(''));

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(501, $result->getStatusCode());
        $this->assertSame(
            '{"error":"Sorry! Something is wrong. Exception code #1483531241"}',
            (string)$result->getBody()
        );
    }

    /**
     * @param $response
     * @return RouterInterface
     */
    private function buildRouter($response)
    {
        /** @var RouterInterface|ObjectProphecy $router */
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

    public function __sleep()
    {
        $properties = get_object_vars($this);

        // Do not try to serialize the `exceptionHandler` callback (only happens in case of an error)
        unset($properties['exceptionHandler']);

        return $properties;
    }
}
