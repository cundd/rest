<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 04.01.17
 * Time: 12:27
 */

namespace Cundd\Rest\Tests\Unit\Router;


use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Exception\NotFoundException;
use Cundd\Rest\Router\ResultConverter;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;


class ResultConverterTest extends \PHPUnit_Framework_TestCase
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
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->responseFactory = new ResponseFactory($this->prophesize(RequestFactoryInterface::class)->reveal());
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
        $this->fixture = new ResultConverter($this->buildRouter('some result'), $this->responseFactory);

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
        $this->fixture = new ResultConverter($this->buildRouter(new NotFoundException()), $this->responseFactory);

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
            $this->responseFactory
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
        $this->fixture = new ResultConverter($this->buildRouter('some result'), $this->responseFactory);

        $this->fixture->dispatch($request);
    }

    /**
     * @test
     */
    public function dispatchWillPassthroughResponseTest()
    {
        /** @var ResponseInterface|ObjectProphecy $response */
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $this->fixture = new ResultConverter($this->buildRouter($response), $this->responseFactory);

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
            ), $this->responseFactory
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
            $this->responseFactory
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

        if (is_callable($response)) {
            $router->dispatch(Argument::any())->will($response);
        } else {
            $router->dispatch(Argument::any())->willReturn($response);
        }

        return $router->reveal();
    }
}
