<?php

/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 21:13
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\ObjectManager;
use Cundd\Rest\Tests\Functional\AbstractCase;

class ObjectManagerTest extends AbstractCase
{
    /**
     * @var ObjectManager
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        require_once __DIR__ . '/../../FixtureClasses.php';
        $this->fixture = new ObjectManager();
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
    public function getDataProviderTest($url, $expectedClass, $classToBuild = [])
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

        return [
            //     url,                expected,                     classToBuild
            [
                '',
                'Cundd\\Rest\\DataProvider\\DataProvider',
                [],
            ],
            [
                'my_ext-my_model/1',
                'Tx_MyExt_Rest_DataProvider',
                ['Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider],
            ],
            [
                'my_ext-my_model/1.json',
                'Tx_MyExt_Rest_DataProvider',
                ['Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider],
            ],
            [
                'MyExt-MyModel/1',
                'Tx_MyExt_Rest_DataProvider',
                ['Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider],
            ],
            [
                'MyExt-MyModel/1.json',
                'Tx_MyExt_Rest_DataProvider',
                ['Tx_MyExt_Rest_DataProvider', '', $defaultDataProvider],
            ],
            [
                'vendor-my_second_ext-my_model/1',
                '\\Vendor\\MySecondExt\\Rest\\DataProvider',
                ['DataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider],
            ],
            [
                'Vendor-MySecondExt-MyModel/1',
                '\\Vendor\\MySecondExt\\Rest\\DataProvider',
                ['DataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider],
            ],
            [
                'Vendor-NotExistingExt-MyModel/1',
                $defaultDataProvider,
            ],
            [
                'Vendor-NotExistingExt-MyModel/1.json',
                $defaultDataProvider,
            ],
            [
                'MyThirdExt-MyModel/1.json',
                'Tx_MyThirdExt_Rest_MyModelDataProvider',
                ['Tx_MyThirdExt_Rest_MyModelDataProvider', '', $defaultDataProvider],
            ],
            [
                'Vendor-MySecondExt-MyModel/1.json',
                '\\Vendor\\MySecondExt\\Rest\\MyModelDataProvider',
                ['MyModelDataProvider', 'Vendor\\MySecondExt\\Rest', $defaultDataProvider],
            ],
            [
                'virtual_object-page',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ],
            [
                'virtual_object-page.json',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ],
            [
                'virtual_object-page/1',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ],
            [
                'virtual_object-page/1.json',
                'Cundd\Rest\DataProvider\VirtualObjectDataProvider',
            ],
        ];
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
    public function getHandlerTest($url, $expectedClass, $classToBuild = [])
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

        return [
            //     url,                expected,                     classToBuild
            [
                'my_ext-my_model/1',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $defaultHandler],
            ],
            [
                'my_ext-my_model/1.json',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $defaultHandler],
            ],
            [
                'MyExt-MyModel/1',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $defaultHandler],
            ],
            [
                'MyExt-MyModel/1.json',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $defaultHandler],
            ],
            [
                'vendor-my_second_ext-my_model/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler],
            ],
            [
                'Vendor-MySecondExt-MyModel/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler],
            ],
            [
                'Vendor-MySecondExt-WhatEver/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler],
            ],
            [
                'Vendor-MySecondExt-WhatEver/',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler],
            ],
            [
                'Vendor-MySecondExt-WhatEver',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest\\', $defaultHandler],
            ],
            [
                'Vendor-NotExistingExt-MyModel/1',
                $defaultHandler,
            ],
            [
                'Vendor-NotExistingExt-MyModel/1.json',
                $defaultHandler,
            ],
        ];
    }
}
