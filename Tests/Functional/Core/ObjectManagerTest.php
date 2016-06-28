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

/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 21:13
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Tests\Functional\AbstractCase;

require_once __DIR__ . '/../AbstractCase.php';

class ObjectManagerTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\ObjectManager
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        require_once __DIR__ . '/../../FixtureClasses.php';
        $this->fixture = new \Cundd\Rest\ObjectManager();
    }

    public function tearDown()
    {
        // Reset the last request
        if ($this->fixture) {
            $this->fixture->getRequestFactory()->resetRequest();
        }
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getRequestFactoryTest()
    {
        $object = $this->fixture->getRequestFactory();
        $this->assertInstanceOf('Cundd\\Rest\\RequestFactoryInterface', $object);
        $this->assertInstanceOf('Cundd\\Rest\\RequestFactory', $object);
    }

    /**
     * @test
     */
    public function getResponseFactoryTest()
    {
        $object = $this->fixture->getResponseFactory();
        $this->assertInstanceOf('Cundd\\Rest\\ResponseFactoryInterface', $object);
        $this->assertInstanceOf('Cundd\\Rest\\ResponseFactory', $object);
    }

    /**
     * @test
     */
    public function getConfigurationProviderTest()
    {
        $object = $this->fixture->getConfigurationProvider();
        $this->assertInstanceOf('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider', $object);
    }

    /**
     * @test
     */
    public function getDataProviderTest()
    {
        $object = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Cundd\\Rest\\DataProvider\\DataProviderInterface', $object);
        $this->assertInstanceOf('Cundd\\Rest\\DataProvider\\DataProvider', $object);
    }

    /**
     * @test
     */
    public function getAuthenticationProviderTest()
    {
        $object = $this->fixture->getAuthenticationProvider();
        $this->assertInstanceOf('Cundd\\Rest\\Authentication\\AuthenticationProviderInterface', $object);
    }

    /**
     * @test
     */
    public function getDataProviderForPathTest()
    {
        $_GET['u'] = 'my_ext-my_model/1';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDataProviderForPathWithFormatTest()
    {
        $_GET['u'] = 'my_ext-my_model/1.json';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDataProviderForPathUpperCamelCaseTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDataProviderForPathUpperCamelCaseWithFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getNamespacedDataProviderForPathTest()
    {
        $_GET['u'] = 'vendor-my_second_ext-my_model/1';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getNamespacedDataProviderForPathUpperCamelCaseTest()
    {
        $_GET['u'] = 'Vendor-MySecondExt-MyModel/1';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDefaultDataProviderForPathTest()
    {
        $_GET['u'] = 'Vendor-NotExistingExt-MyModel/1';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('\\Cundd\\Rest\\DataProvider\\DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDefaultDataProviderForPathWithFormatTest()
    {
        $_GET['u'] = 'Vendor-NotExistingExt-MyModel/1.json';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('\\Cundd\\Rest\\DataProvider\\DataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getHandlerForPathTest()
    {
        $_GET['u'] = 'my_ext-my_model/1';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('Tx_MyExt_Rest_Handler', $handler);
    }

    /**
     * @test
     */
    public function getHandlerForPathWithFormatTest()
    {
        $_GET['u'] = 'my_ext-my_model/1.json';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('Tx_MyExt_Rest_Handler', $handler);
    }

    /**
     * @test
     */
    public function getHandlerForPathUpperCamelCaseTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('Tx_MyExt_Rest_Handler', $handler);
    }

    /**
     * @test
     */
    public function getHandlerForPathUpperCamelCaseWithFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('Tx_MyExt_Rest_Handler', $handler);
    }

    /**
     * @test
     */
    public function getNamespacedHandlerForPathTest()
    {
        $_GET['u'] = 'vendor-my_second_ext-my_model/1';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\Handler', $handler);
    }

    /**
     * @test
     */
    public function getNamespacedHandlerForPathUpperCamelCaseTest()
    {
        $_GET['u'] = 'Vendor-MySecondExt-MyModel/1';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\Handler', $handler);
    }

    /**
     * @test
     */
    public function getDefaultHandlerForPathTest()
    {
        $_GET['u'] = 'Vendor-NotExistingExt-MyModel/1';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('\\Cundd\\Rest\\Handler', $handler);
    }

    /**
     * @test
     */
    public function getDefaultHandlerForPathWithFormatTest()
    {
        $_GET['u'] = 'Vendor-NotExistingExt-MyModel/1.json';
        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf('\\Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('\\Cundd\\Rest\\Handler', $handler);
    }



    /**
     * @test
     */
    public function getDataProviderForVirtualObjectTest()
    {
        $_GET['u'] = 'VirtualObject-Page';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Cundd\Rest\DataProvider\VirtualObjectDataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDataProviderForVirtualObjectWithFormatTest()
    {
        $_GET['u'] = 'VirtualObject-Page.json';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Cundd\Rest\DataProvider\VirtualObjectDataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDataProviderForVirtualObjectWithPathTest()
    {
        $_GET['u'] = 'VirtualObject-Page/1';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Cundd\Rest\DataProvider\VirtualObjectDataProvider', $dataProvider);
    }

    /**
     * @test
     */
    public function getDataProviderForVirtualObjectWithPathWithFormatTest()
    {
        $_GET['u'] = 'VirtualObject-Page/1.json';
        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf('Cundd\Rest\DataProvider\VirtualObjectDataProvider', $dataProvider);
    }
}
