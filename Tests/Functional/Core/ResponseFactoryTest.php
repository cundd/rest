<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 05.08.15
 * Time: 22:03
 */

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\Tests\Functional\AbstractCase;

require_once __DIR__ . '/../AbstractCase.php';

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
        $response = $this->fixture->createErrorResponse('Everything ok', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"error":"Everything ok"}', $response->content());

        $this->requestFactory->getRequest()->format('html');
        $response = $this->fixture->createErrorResponse('HTML format is currently not supported', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Unsupported format: html. Please set the Accept header to application/json', $response->content());

        $this->requestFactory->getRequest()->format('blur');
        $response = $this->fixture->createErrorResponse('This will default to JSON', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"error":"This will default to JSON"}', $response->content());

        $response = $this->fixture->createErrorResponse(null, 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"error":"OK"}', $response->content());

        $response = $this->fixture->createErrorResponse(null, 404);
        $this->assertEquals(404, $response->status());
        $this->assertEquals('{"error":"Not Found"}', $response->content());
    }

    /**
     * @test
     */
    public function createSuccessResponseTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $response = $this->fixture->createSuccessResponse('Everything ok', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"message":"Everything ok"}', $response->content());

        $this->requestFactory->getRequest()->format('html');
        $response = $this->fixture->createSuccessResponse('HTML format is currently not supported', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Unsupported format: html. Please set the Accept header to application/json', $response->content());

        $this->requestFactory->getRequest()->format('blur');
        $response = $this->fixture->createSuccessResponse('This will default to JSON', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"message":"This will default to JSON"}', $response->content());

        $response = $this->fixture->createSuccessResponse(null, 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"message":"OK"}', $response->content());

        // This will be an error
        $response = $this->fixture->createSuccessResponse(null, 404);
        $this->assertEquals(404, $response->status());
        $this->assertEquals('{"error":"Not Found"}', $response->content());
    }
}
