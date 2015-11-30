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
 * Test case for class new \Cundd\Rest\RequestFactory
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class RequestFactoryTest extends AbstractCase {
    /**
     * @var RequestFactoryInterface
     */
    protected $fixture;

    public function setUp() {
        parent::setUp();
        require_once __DIR__ . '/../../FixtureClasses.php';

        /** @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider|\PHPUnit_Framework_MockObject_MockObject $configurationProviderMock */
        $configurationProviderMock = $this->getMockBuilder('Cundd\Rest\Configuration\TypoScriptConfigurationProvider')
            ->getMock();

        $valueMap = array(
            array('aliases.myAlias', null, 'MyExt-MyModel'),
        );
        $configurationProviderMock
            ->expects($this->any())
            ->method('getSetting')
            ->will($this->returnValueMap($valueMap));

        $this->fixture = new \Cundd\Rest\RequestFactory();
        $this->fixture->injectConfigurationProvider($configurationProviderMock);
    }

    public function tearDown() {
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
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function getUriWithFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/2.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel/2', $request->url());
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function getUriWithHtmlFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/2.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel/2', $request->url());
        $this->assertEquals('html', $request->format());
    }

    /**
     * @test
     */
    public function getAliasUriTest() {
        $_GET['u'] = 'myAlias/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel/1', $request->url());
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function getAliasUriWithFormatTest() {
        $_GET['u'] = 'myAlias/2.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel/2', $request->url());
        $this->assertEquals('json', $request->format());
    }

    /**
     * @test
     */
    public function getAliasUriWithHtmlFormatTest() {
        $_GET['u'] = 'myAlias/2.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel/2', $request->url());
        $this->assertEquals('html', $request->format());
    }

    /**
     * @test
     */
    public function getOriginalPathTest() {
        $_GET['u'] = 'MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->originalPath());
    }

    /**
     * @test
     */
    public function getOriginalPathWithFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/2.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->originalPath());
    }

    /**
     * @test
     */
    public function getRootObjectKeyTest() {
        $_GET['u'] = 'MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getRootObjectKey());
    }

    /**
     * @test
     */
    public function getRootObjectKeyWithFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/2.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getRootObjectKey());
    }

    /**
     * @test
     */
    public function getDocumentUriTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('Document-MyExt-MyModel/1', $request->url());
        $this->assertEquals('json', $request->format());
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
    public function getDocumentUriWithHtmlFormatTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('Document-MyExt-MyModel/1', $request->url());
        $this->assertEquals('html', $request->format());
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
    public function getOriginalPathWithDocumentTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1';
        $path = $this->fixture->getRequest()->originalPath();
        $this->assertEquals('Document-MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getOriginalPathWithDocumentWithFormatTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1.json';
        $path = $this->fixture->getRequest()->originalPath();
        $this->assertEquals('Document-MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getRootObjectKeyWithDocumentTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1';
        $path = $this->fixture->getRequest()->getRootObjectKey();
        $this->assertEquals('MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getRootObjectKeyWithDocumentWithFormatTest() {
        $_GET['u'] = 'Document/MyExt-MyModel/1.json';
        $path = $this->fixture->getRequest()->getRootObjectKey();
        $this->assertEquals('MyExt-MyModel', $path);
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
        $this->assertEquals('json', $request->format());
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
    public function getFormatWithHtmlFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/1.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('html', $request->format());
    }

    /**
     * @test
     */
    public function getFormatWithNotExistingFormatTest() {
        $_GET['u'] = 'MyExt-MyModel/1.blur';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->format());
    }
}
