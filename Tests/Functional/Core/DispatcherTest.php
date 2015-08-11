<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;

require_once __DIR__ . '/../AbstractCase.php';

/**
 * Test case for class new \Cundd\Rest\App
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class DispatcherTest extends AbstractCase {
    /**
     * @var \Cundd\Rest\Dispatcher
     */
    protected $fixture;

    public function setUp() {
        parent::setUp();
        require_once __DIR__ . '/../../FixtureClasses.php';
        $this->fixture = new \Cundd\Rest\Dispatcher();
    }

    public function tearDown() {
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = $this->objectManager->get('Cundd\\Rest\\RequestFactory');
        $requestFactory->resetRequest();
        unset($this->fixture);
        unset($_GET['u']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getUriTest() {
        $_GET['u'] = 'MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel/1', $request->url());
        $this->assertEquals('html', $request->format());
    }

    /**
     * @test
     */
    public function getUriWithFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel/1', $request->url());
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function getDocumentUriTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('Document-MyExt-MyModel/1', $request->url());
        $this->assertEquals('html', $request->format());
    }

    /**
     * @test
     */
    public function getDocumentUriWithFormatTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('Document-MyExt-MyModel/1', $request->url());
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function getPathTest() {
        $_GET['u'] = 'MyExt-MyModel/1';
        $path = $this->fixture->getRequest()->path();
        $this->assertEquals('MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getPathWithFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $path = $this->fixture->getRequest()->path();
        $this->assertEquals('MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getDocumentPathTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1';
        $path = $this->fixture->getRequest()->path();
        $this->assertEquals('Document-MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getDocumentPathWithFormatTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1.json';
        $path = $this->fixture->getRequest()->path();
        $this->assertEquals('Document-MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getUnderscoredPathWithFormatAndIdTest() {
        $_GET['u'] = 'my_ext-my_model/1.json';
        $path = $this->fixture->getRequest()->path();
        $this->assertEquals('my_ext-my_model', $path);
    }

    /**
     * @test
     */
    public function getUnderscoredPathWithFormatTest2() {
        $_GET['u'] = 'my_ext-my_model.json';
        $path = $this->fixture->getRequest()->path();
        $this->assertEquals('my_ext-my_model', $path);
    }

    /**
     * @test
     */
    public function getFormatWithoutFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('html', $request->format());
    }

    /**
     * @test
     */
    public function getFormatWithFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function getFormatWithNotExistingFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/1.blur';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function createErrorResponseTest() {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $response = $this->fixture->createErrorResponse('Everything ok', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"error":"Everything ok"}', $response->content());

        $this->fixture->getRequest()->format('html');
        $response = $this->fixture->createErrorResponse('HTML format is currently not supported', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Unsupported format: html. Please set the Accept header to application/json', $response->content());

        $this->fixture->getRequest()->format('blur');
        $response = $this->fixture->createErrorResponse('This will default to JSON', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"error":"This will default to JSON"}', $response->content());

        $response = $this->fixture->createErrorResponse(NULL, 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"error":"OK"}', $response->content());

        $response = $this->fixture->createErrorResponse(NULL, 404);
        $this->assertEquals(404, $response->status());
        $this->assertEquals('{"error":"Not Found"}', $response->content());
    }

    /**
     * @test
     */
    public function createSuccessResponseTest() {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $response = $this->fixture->createSuccessResponse('Everything ok', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"message":"Everything ok"}', $response->content());

        $this->fixture->getRequest()->format('html');
        $response = $this->fixture->createSuccessResponse('HTML format is currently not supported', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Unsupported format: html. Please set the Accept header to application/json', $response->content());

        $this->fixture->getRequest()->format('blur');
        $response = $this->fixture->createSuccessResponse('This will default to JSON', 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"message":"This will default to JSON"}', $response->content());

        $response = $this->fixture->createSuccessResponse(NULL, 200);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('{"message":"OK"}', $response->content());

        // This will be an error
        $response = $this->fixture->createSuccessResponse(NULL, 404);
        $this->assertEquals(404, $response->status());
        $this->assertEquals('{"error":"Not Found"}', $response->content());
    }


}
