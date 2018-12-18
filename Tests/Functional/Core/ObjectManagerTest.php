<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\Authentication\CredentialsAuthenticationProvider;
use Cundd\Rest\Authentication\RequestAuthenticationProvider;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\VirtualObjectDataProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

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
        $this->registerLoggerImplementation();

        $this->fixture = new ObjectManager();
        $this->injectConfigurationProviderUsingHandlerClass('');
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
        $this->assertInstanceOf(RequestFactoryInterface::class, $object);
        $this->assertInstanceOf(RequestFactory::class, $object);
    }

    /**
     * @test
     */
    public function getResponseFactoryTest()
    {
        $object = $this->fixture->getResponseFactory();
        $this->assertInstanceOf(ResponseFactoryInterface::class, $object);
        $this->assertInstanceOf(ResponseFactory::class, $object);
    }

    /**
     * @test
     */
    public function getConfigurationProviderTest()
    {
        $this->fixture = new ObjectManager();
        $object = $this->fixture->getConfigurationProvider();
        $this->assertInstanceOf(TypoScriptConfigurationProvider::class, $object);
    }

    /**
     * @test
     */
    public function getAuthenticationProviderTest()
    {
        $this->injectConfigurationProviderUsingHandlerClass('', ['authenticationProvider' => []]);
        $object = $this->fixture->getAuthenticationProvider();
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
    }

    /**
     * @test
     */
    public function getAuthenticationProviderFromConfigurationTest()
    {
        $this->injectConfigurationProviderUsingHandlerClass(
            '',
            [
                'authenticationProvider' => [
                    30 => RequestAuthenticationProvider::class,
                    50 => CredentialsAuthenticationProvider::class,
                    10 => BasicAuthenticationProvider::class,
                ],
            ]
        );
        /** @var AuthenticationProviderCollection $object */
        $object = $this->fixture->getAuthenticationProvider();
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
        $this->assertCount(3, $object->getProviders());
        $providers = array_values(iterator_to_array($object->getProviders()));
        $this->assertInstanceOf(BasicAuthenticationProvider::class, $providers[0]);
        $this->assertInstanceOf(RequestAuthenticationProvider::class, $providers[1]);
        $this->assertInstanceOf(CredentialsAuthenticationProvider::class, $providers[2]);
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
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
        $this->assertInstanceOf(DataProvider::class, $dataProvider);
    }

    public function dataProviderTestGenerator()
    {
        $defaultDataProvider = DataProvider::class;

        return [
            // URL,
            // Expected result class,
            // Class to Build
            [
                '',
                DataProvider::class,
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
                VirtualObjectDataProvider::class,
            ],
            [
                'virtual_object-page.json',
                VirtualObjectDataProvider::class,
            ],
            [
                'virtual_object-page/1',
                VirtualObjectDataProvider::class,
            ],
            [
                'virtual_object-page/1.json',
                VirtualObjectDataProvider::class,
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
        $_GET['u'] = $url;
        if ($classToBuild) {
            $this->buildClass($classToBuild);
        }

        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf($expectedClass, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertInstanceOf(CrudHandler::class, $handler);
    }

    public function handlerTestGenerator()
    {
        $defaultHandler = CrudHandler::class;

        return [
            // URL,
            // Expected result class,
            // Class to Build
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

    /**
     * @test
     */
    public function getHandlerFromResourceTest()
    {
        $expectedHandler = 'Tx_MyExt_Rest_Handler' . time();
        $this->buildClass([$expectedHandler, '', CrudHandler::class]);

        $resourceType = new ResourceType('my_secondext-my_model');
        $resourceTypeString = (string)$resourceType;
        $configurationProvider = new TypoScriptConfigurationProvider();
        $configurationProvider->setSettings(
            [
                'paths' => [
                    $resourceTypeString => [
                        'handlerClass' => $expectedHandler,
                    ],
                ],
            ]
        );
        $this->injectPropertyIntoObject($configurationProvider, 'configurationProvider', $this->fixture);

        $_GET['u'] = $resourceTypeString;

        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf($expectedHandler, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertInstanceOf(CrudHandler::class, $handler);
    }

    /**
     * @param string $handler
     */
    private function injectConfigurationProviderUsingHandlerClass($handler, array $settings = [])
    {
        /** @var ObjectProphecy|ResourceConfiguration $resourceConfiguration */
        $resourceConfiguration = $this->prophesize(ResourceConfiguration::class);
        /** @var MethodProphecy|string $handlerClass */
        $handlerClass = $resourceConfiguration->getHandlerClass();
        $handlerClass->willReturn($handler);
        /** @var ObjectProphecy|ConfigurationProviderInterface $configurationProvider */
        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class);
        /** @var ResourceType $resourceType */
        $resourceType = Argument::any();
        /** @var MethodProphecy|ResourceConfiguration $methodProphecy */
        $methodProphecy = $configurationProvider->getResourceConfiguration($resourceType);
        $methodProphecy
            ->willReturn($resourceConfiguration->reveal());

        /** @var MethodProphecy $getSettingsProphecy */
        $getSettingsProphecy = $configurationProvider->getSetting(Argument::type('string'));
        $getSettingsProphecy->will(
            function ($args) use ($settings) {
                return isset($settings[$args[0]]) ? $settings[$args[0]] : null;
            }
        );
        $this->injectPropertyIntoObject($configurationProvider->reveal(), 'configurationProvider', $this->fixture);
    }
}
