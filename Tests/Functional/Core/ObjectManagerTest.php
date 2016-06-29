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
     * @param array $classToBuild
     * @throws \Exception
     */
    public function getDataProviderTest($url, $expectedClass, $classToBuild = array())
    {
        $_GET['u'] = $url;
        if ($classToBuild) {
            $this->createClass($classToBuild);
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
            array('', 'Cundd\\Rest\\DataProvider\\DataProvider', array()),
            array('my_ext-my_model/1', 'Tx_MyExt_Rest_DataProvider', array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider)),
            array('my_ext-my_model/1.json', 'Tx_MyExt_Rest_DataProvider', array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider)),
            array('MyExt-MyModel/1', 'Tx_MyExt_Rest_DataProvider', array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider)),
            array('MyExt-MyModel/1.json', 'Tx_MyExt_Rest_DataProvider', array('Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider)),

            array('vendor-my_second_ext-my_model/1', '\\Vendor\\MySecondExt\\Rest\\DataProvider', array('DataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider)),
            array('Vendor-MySecondExt-MyModel/1', '\\Vendor\\MySecondExt\\Rest\\DataProvider', array('DataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider)),
            array('Vendor-NotExistingExt-MyModel/1', $defaultDataProvider),
            array('Vendor-NotExistingExt-MyModel/1.json', $defaultDataProvider),

            array('MyThirdExt-MyModel/1.json', 'Tx_MyThirdExt_Rest_MyModelDataProvider', array('Tx_MyThirdExt_Rest_MyModelDataProvider', '', $defaultDataProvider)),
            array('Vendor-MySecondExt-MyModel/1.json', '\\Vendor\\MySecondExt\\Rest\\MyModelDataProvider', array('MyModelDataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider)),

            array('VirtualObject-Page', 'Cundd\Rest\DataProvider\VirtualObjectDataProvider'),
            array('VirtualObject-Page.json', 'Cundd\Rest\DataProvider\VirtualObjectDataProvider'),
            array('VirtualObject-Page/1', 'Cundd\Rest\DataProvider\VirtualObjectDataProvider'),
            array('VirtualObject-Page/1.json', 'Cundd\Rest\DataProvider\VirtualObjectDataProvider'),
        );
    }

    /**
     * @test
     *
     * @dataProvider handlerTestGenerator
     * @param string $url
     * @param string $expectedClass
     * @param array $classToBuild
     * @throws \Exception
     */
    public function getHandlerTest($url, $expectedClass, $classToBuild = array())
    {
        $_GET['u'] = $url;
        if ($classToBuild) {
            $this->createClass($classToBuild);
        }

        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf($expectedClass, $handler);
        $this->assertInstanceOf('Cundd\\Rest\\HandlerInterface', $handler);
        $this->assertInstanceOf('Cundd\\Rest\\Handler', $handler);
    }

    public function handlerTestGenerator()
    {
        $defaultHandler = '\\Cundd\\Rest\\Handler';

        return array(
            //     url,                expected,                     classToBuild
            array('my_ext-my_model/1', 'Tx_MyExt_Rest_Handler', array('Tx_MyExt_Rest_Handler', '', $defaultHandler)),
            array('my_ext-my_model/1.json', 'Tx_MyExt_Rest_Handler', array('Tx_MyExt_Rest_Handler', '', $defaultHandler)),
            array('MyExt-MyModel/1', 'Tx_MyExt_Rest_Handler', array('Tx_MyExt_Rest_Handler', '', $defaultHandler)),
            array('MyExt-MyModel/1.json', 'Tx_MyExt_Rest_Handler', array('Tx_MyExt_Rest_Handler', '', $defaultHandler)),

            array('vendor-my_second_ext-my_model/1', '\\Vendor\\MySecondExt\\Rest\\Handler', array('Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler)),
            array('Vendor-MySecondExt-MyModel/1', '\\Vendor\\MySecondExt\\Rest\\Handler', array('Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler)),

            array('Vendor-NotExistingExt-MyModel/1', $defaultHandler),
            array('Vendor-NotExistingExt-MyModel/1.json', $defaultHandler),
        );
    }

    /**
     * @param string $className
     * @param string $namespace
     * @param string $extends
     * @throws \Exception
     */
    private function createClass($className, $namespace = '', $extends = '')
    {
        if (func_num_args() === 1 && is_array($className)) {
            list($className, $namespace, $extends) = $className;
        }
        if (!is_string($className)) {
            throw new \InvalidArgumentException('$className must be a string');
        }
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException('$namespace must be a string');
        }
        if (!is_string($extends)) {
            throw new \InvalidArgumentException('$extends must be a string');
        }

        $namespace = trim($namespace, '\\');
        if (class_exists("$namespace\\$className")) {
            printf('Class %s already exists' . PHP_EOL, "$namespace\\$className");
            return;
        }

        $code = array();
        if ($namespace) {
            $code[] = "namespace $namespace;";
        }
        $code[] = "class $className";
        if ($extends) {
            $code[] = "extends $extends";
        }
        $code[] = '{}';

        eval(implode(' ', $code));

        if (!class_exists("$namespace\\$className")) {
            throw new \Exception(sprintf('Could not create class %s', "$namespace\\$className"));
        }
    }


//    /**
//     * @test
//     */
//    public function getDataProviderForPathTest()
//    {
//        $_GET['u'] = 'my_ext-my_model/1';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
//    }

//    /**
//     * @test
//     */
//    public function getDataProviderForPathWithFormatTest()
//    {
//        $_GET['u'] = 'my_ext-my_model/1.json';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
//    }

//    /**
//     * @test
//     */
//    public function getDataProviderForPathUpperCamelCaseTest()
//    {
//        $_GET['u'] = 'MyExt-MyModel/1';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
//    }
//
//    /**
//     * @test
//     */
//    public function getDataProviderForPathUpperCamelCaseWithFormatTest()
//    {
//        $_GET['u'] = 'MyExt-MyModel/1.json';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
//    }

//    /**
//     * @test
//     */
//    public function getNamespacedDataProviderForPathTest()
//    {
//        $_GET['u'] = 'vendor-my_second_ext-my_model/1';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\DataProvider', $dataProvider);
//    }

//    /**
//     * @test
//     */
//    public function getNamespacedDataProviderForPathUpperCamelCaseTest()
//    {
//        $_GET['u'] = 'Vendor-MySecondExt-MyModel/1';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\DataProvider', $dataProvider);
//    }

//    /**
//     * @test
//     */
//    public function getDefaultDataProviderForPathTest()
//    {
//        $_GET['u'] = 'Vendor-NotExistingExt-MyModel/1';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('\\Cundd\\Rest\\DataProvider\\DataProvider', $dataProvider);
//    }

//    /**
//     * @test
//     */
//    public function getDefaultDataProviderForPathWithFormatTest()
//    {
//        $_GET['u'] = 'Vendor-NotExistingExt-MyModel/1.json';
//        $dataProvider = $this->fixture->getDataProvider();
//        $this->assertInstanceOf('\\Cundd\\Rest\\DataProvider\\DataProvider', $dataProvider);
//    }
//
}
