<?php

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\Tests\Functional\AbstractCase;

class ResponseFactoryTest extends AbstractCase
{
    /**
     * @var ResponseFactory
     */
    protected $fixture;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();
        $this->requestFactory = $this->objectManager->get('Cundd\Rest\RequestFactoryInterface');
        $this->fixture = $this->objectManager->get('Cundd\Rest\ResponseFactory');
    }

    /**
     * @test
     */
    public function createErrorResponseTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $response = $this->fixture->createErrorResponse('Everything ok', 200, $this->requestFactory->getRequest());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"error":"Everything ok"}', (string)$response->getBody());

        $this->requestFactory->registerCurrentRequest(
            $this->requestFactory->getRequest()->withFormat(Format::formatHtml())
        );
        $response = $this->fixture->createErrorResponse(
            'HTML format is currently not supported',
            200,
            $this->requestFactory->getRequest()
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            'Unsupported format: html. Please set the Accept header to application/json',
            (string)$response->getBody()
        );

        $this->requestFactory->registerCurrentRequest(
            $this->requestFactory->getRequest()->withFormat(Format::formatJson())
        );
        $response = $this->fixture->createErrorResponse(null, 200, $this->requestFactory->getRequest());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"error":"OK"}', (string)$response->getBody());

        $response = $this->fixture->createErrorResponse(null, 404, $this->requestFactory->getRequest());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"error":"Not Found"}', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function createErrorResponseWithRequestArgumentTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $response = $this->fixture->createErrorResponse('Everything ok', 200, $this->requestFactory->getRequest());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"error":"Everything ok"}', (string)$response->getBody());

        $response = $this->fixture->createErrorResponse(
            'HTML format is currently not supported',
            200,
            $this->requestFactory->getRequest()->withFormat(Format::formatHtml())
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            'Unsupported format: html. Please set the Accept header to application/json',
            (string)$response->getBody()
        );

        $response = $this->fixture->createErrorResponse(
            null,
            200,
            $this->requestFactory->getRequest()->withFormat(Format::formatJson())
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"error":"OK"}', (string)$response->getBody());

        $response = $this->fixture->createErrorResponse(
            null,
            404,
            $this->requestFactory->getRequest()->withFormat(Format::formatJson())
        );
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"error":"Not Found"}', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function createSuccessResponseTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $response = $this->fixture->createSuccessResponse('Everything ok', 200, $this->requestFactory->getRequest());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message":"Everything ok"}', (string)$response->getBody());

        $this->requestFactory->registerCurrentRequest(
            $this->requestFactory->getRequest()->withFormat(Format::formatHtml())
        );
        $response = $this->fixture->createSuccessResponse(
            'HTML format is currently not supported',
            200,
            $this->requestFactory->getRequest()
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            'Unsupported format: html. Please set the Accept header to application/json',
            (string)$response->getBody()
        );

        $this->requestFactory->registerCurrentRequest(
            $this->requestFactory->getRequest()->withFormat(Format::formatJson())
        );
        $response = $this->fixture->createSuccessResponse(null, 200, $this->requestFactory->getRequest());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message":"OK"}', (string)$response->getBody());

        // This will be an error
        $response = $this->fixture->createSuccessResponse(null, 404, $this->requestFactory->getRequest());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"error":"Not Found"}', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function createSuccessResponseWithRequestArgumentTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $response = $this->fixture->createSuccessResponse('Everything ok', 200, $this->requestFactory->getRequest());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message":"Everything ok"}', (string)$response->getBody());

        $response = $this->fixture->createSuccessResponse(
            'HTML format is currently not supported',
            200,
            $this->requestFactory->getRequest()->withFormat(Format::formatHtml())
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            'Unsupported format: html. Please set the Accept header to application/json',
            (string)$response->getBody()
        );

        $response = $this->fixture->createSuccessResponse(
            null,
            200,
            $this->requestFactory->getRequest()->withFormat(Format::formatJson())
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message":"OK"}', (string)$response->getBody());

        // This will be an error
        $response = $this->fixture->createSuccessResponse(
            null,
            404,
            $this->requestFactory->getRequest()->withFormat(Format::formatJson())
        );
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"error":"Not Found"}', (string)$response->getBody());
    }
}
