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
    public function getAuthenticationProviderTest()
    {
        $object = $this->fixture->getAuthenticationProvider();
        $this->assertInstanceOf('Cundd\\Rest\\Authentication\\AuthenticationProviderInterface', $object);
    }

    /**
     * @test
     * @dataProvider dataProviderTestGenerator
     * @param string $url
     * @param string $expectedClass
     * @param array  $classToBuild
     * @throws \Exception
     */
    public function getDataProviderTest($url, $expectedClass, $classToBuild = array())
    {
        $_GET['u'] = $url;
        if ($classToBuild) {
            $this->buildClass($classToBuild);
        }

        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf($expectedClass, $dataProvider);
        $this->assertInstanceOf('Cundd\\Rest\\DataProvider\\DataProviderInterface', $dataProvider);
        $this->assertInstanceOf('Cundd\\Rest\\DataProvider\\DataProvider', $dataProvider);
    }

    public function dataProviderTestGenerator()
    {
        $defaultDataProvider = '\\Cundd\\Rest\\DataProvider\\DataProvider';

        return array(
            //     url,                expected,                     classToBuild
            array(
                '',
                'Cundd\\Rest\\DataProvider\\DataProvider',
                array(),
            ),
            array(
                'my_ext-my_model/1',
                'Tx_MyExt_Rest_DataProvider',
                array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider),
            ),
            array(
                'my_ext-my_model/1.json',
                'Tx_MyExt_Rest_DataProvider',
                array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider),
            ),
            array(
                'MyExt-MyModel/1',
                'Tx_MyExt_Rest_DataProvider',
                array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider),
            ),
            array(
                'MyExt-MyModel/1.json',
                'Tx_MyExt_Rest_DataProvider',
                array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider),
            ),
            array(
                'vendor-my_second_ext-my_model/1',
                '\\Vendor\\MySecondExt\\Rest\\DataProvider',
                array('DataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider),
            ),
            array(
                'Vendor-MySecondExt-MyModel/1',
                '\\Vendor\\MySecondExt\\Rest\\DataProvider',
                array('DataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider),
            ),
            array(
                'Vendor-NotExistingExt-MyModel/1',
                $defaultDataProvider,
            ),
            array(
                'Vendor-NotExistingExt-MyModel/1.json',
                $defaultDataProvider,
            ),
            array(
                'MyThirdExt-MyModel/1.json',
                'Tx_MyThirdExt_Rest_MyModelDataProvider',
                array('Tx_MyThirdExt_Rest_MyModelDataProvider', '', $defaultDataProvider),
            ),
            array(
                'Vendor-MySecondExt-MyModel/1.json',
                '\\Vendor\\MySecondExt\\Rest\\MyModelDataProvider',
                array('MyModelDataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider),
            ),
            array(
                'virtual_object-page',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ),
            array(
                'virtual_object-page.json',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ),
            array(
                'virtual_object-page/1',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ),
            array(
                'virtual_object-page/1.json',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ),
        );
    }

    /**
     * @test
     *
     * @dataProvider handlerTestGenerator
     * @param string $url
     * @param string $expectedClass
     * @param array  $classToBuild
     * @throws \Exception
     */
    public function getHandlerTest($url, $expectedClass, $classToBuild = array())
    {
//        var_dump(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
//        var_dump(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'));
//        var_dump(GeneralUtility::getIndpEnv('HTTP_HOST'));
        $_GET['u'] = $url;
        if ($classToBuild) {
            $this->buildClass($classToBuild);
        }

        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf($expectedClass, $handler);
        $this->assertInstanceOf('Cundd\\Rest\\Handler\\HandlerInterface', $handler);
        $this->assertInstanceOf('Cundd\\Rest\\Handler\\Handler', $handler);
    }

    public function handlerTestGenerator()
    {
        $defaultHandler = '\\Cundd\\Rest\\Handler\\Handler';

        return array(
            //     url,                expected,                     classToBuild
            array(
                'my_ext-my_model/1',
                'Tx_MyExt_Rest_Handler',
                array('Tx_MyExt_Rest_Handler', '', $defaultHandler),
            ),
            array(
                'my_ext-my_model/1.json',
                'Tx_MyExt_Rest_Handler',
                array('Tx_MyExt_Rest_Handler', '', $defaultHandler),
            ),
            array(
                'MyExt-MyModel/1',
                'Tx_MyExt_Rest_Handler',
                array('Tx_MyExt_Rest_Handler', '', $defaultHandler),
            ),
            array(
                'MyExt-MyModel/1.json',
                'Tx_MyExt_Rest_Handler',
                array('Tx_MyExt_Rest_Handler', '', $defaultHandler),
            ),
            array(
                'vendor-my_second_ext-my_model/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                array('Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler),
            ),
            array(
                'Vendor-MySecondExt-MyModel/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                array('Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler),
            ),
            array(
                'Vendor-NotExistingExt-MyModel/1',
                $defaultHandler,
            ),
            array(
                'Vendor-NotExistingExt-MyModel/1.json',
                $defaultHandler,
            ),
        );
    }
}
