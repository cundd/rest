<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\Authentication\CredentialsAuthenticationProvider;
use Cundd\Rest\Authentication\RequestAuthenticationProvider;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\DataProvider\VirtualObjectDataProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\ClassBuilderTrait;
use Cundd\Rest\Tests\InjectPropertyTrait;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Cundd\Rest\Tests\Unit\Fixtures\Container;
use Cundd\Rest\Tests\Unit\Fixtures\DummyDataProvider;
use Cundd\Rest\Tests\Unit\Fixtures\DummyHandler;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Unit tests for the ObjectManager
 *
 * @see \Cundd\Rest\Tests\Functional\Core\ObjectManagerTest for Functional tests
 */
class ObjectManagerTest extends TestCase
{
    use InjectPropertyTrait;
    use RequestBuilderTrait;
    use ClassBuilderTrait;

    /**
     * @var ObjectManager
     */
    private $fixture;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();
        require_once __DIR__ . '/../../FixtureClasses.php';

        $this->container = new Container();
        $this->fixture = new ObjectManager($this->container);
        $this->injectConfigurationProvider();
    }

    /**
     * @param array  $settings
     * @param string $handler
     * @param string $dataProvider
     */
    private function injectConfigurationProvider(
        array $settings = [],
        string $handler = '',
        string $dataProvider = ''
    ) {
        /** @var ObjectProphecy|ResourceConfiguration $resourceConfiguration */
        $resourceConfiguration = $this->prophesize(ResourceConfiguration::class);
        /** @var MethodProphecy|string $handlerClassMethod */
        $handlerClassMethod = $resourceConfiguration->getHandlerClass();
        $handlerClassMethod->willReturn($handler);

        /** @var MethodProphecy|string $dataProviderClassMethod */
        $dataProviderClassMethod = $resourceConfiguration->getDataProviderClass();
        $dataProviderClassMethod->willReturn($dataProvider);

        /** @var ObjectProphecy|ConfigurationProviderInterface $configurationProvider */
        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class);
        /** @var ResourceType $resourceType */
        $resourceType = Argument::any();
        /** @var MethodProphecy|ResourceConfiguration $methodProphecy */
        $methodProphecy = $configurationProvider->getResourceConfiguration($resourceType);
        $methodProphecy
            ->willReturn($resourceConfiguration->reveal());

        /** @var string $typeToken */
        $typeToken = Argument::type('string');
        /** @var MethodProphecy $getSettingsProphecy */
        $getSettingsProphecy = $configurationProvider->getSetting($typeToken);
        $getSettingsProphecy->will(
            function ($args) use ($settings) {
                return isset($settings[$args[0]]) ? $settings[$args[0]] : null;
            }
        );
        $this->injectPropertyIntoObject($configurationProvider->reveal(), 'configurationProvider', $this->fixture);
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getRequestFactoryTest()
    {
        /** @var ConfigurationProviderInterface $configurationProvider */
        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class)->reveal();
        $this->container->set(
            RequestFactoryInterface::class,
            new RequestFactory($configurationProvider)
        );
        $object = $this->fixture->getRequestFactory();
        $this->assertInstanceOf(RequestFactoryInterface::class, $object);
        $this->assertInstanceOf(RequestFactory::class, $object);
    }

    /**
     * @test
     */
    public function getResponseFactoryTest()
    {
        $this->container->set(ResponseFactoryInterface::class, new ResponseFactory());
        $object = $this->fixture->getResponseFactory();
        $this->assertInstanceOf(ResponseFactoryInterface::class, $object);
        $this->assertInstanceOf(ResponseFactory::class, $object);
    }

    /**
     * @test
     */
    public function getConfigurationProviderTest()
    {
        $this->container->set(ConfigurationProviderInterface::class, new StandaloneConfigurationProvider([]));
        $object = $this->fixture->getConfigurationProvider();
        $this->assertInstanceOf(ConfigurationProviderInterface::class, $object);
    }

    /**
     * @test
     */
    public function getAuthenticationProviderTest()
    {
        $this->container->set(RequestFactoryInterface::class, $this->buildRequestFactory());
        $this->container->set(AuthenticationProviderCollection::class, new AuthenticationProviderCollection([]));

        $this->injectConfigurationProvider(['authenticationProvider' => []], '', '');
        $object = $this->fixture->getAuthenticationProvider();
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
    }

    /**
     * @param string $url
     * @return RequestFactoryInterface
     */
    private function buildRequestFactory(string $url = 'something')
    {
        /** @var ObjectProphecy|RequestFactoryInterface $requestFactoryProphecy */
        $requestFactoryProphecy = $this->prophesize(RequestFactoryInterface::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $requestFactoryProphecy->getRequest()->willReturn($this->buildTestRequest($url));
        $requestFactory = $requestFactoryProphecy->reveal();

        return $requestFactory;
    }

    /**
     * @test
     */
    public function getAuthenticationProviderFromConfigurationTest()
    {
        $this->container->set(RequestFactoryInterface::class, $this->buildRequestFactory());
        $this->container->set(BasicAuthenticationProvider::class, new BasicAuthenticationProvider());
        $this->container->set(RequestAuthenticationProvider::class, new RequestAuthenticationProvider());
        $this->container->set(CredentialsAuthenticationProvider::class, new CredentialsAuthenticationProvider());
        $this->container->set(
            AuthenticationProviderCollection::class,
            function ($a) {
                return new AuthenticationProviderCollection($a);
            }
        );
        $this->injectConfigurationProvider(
            [
                'authenticationProvider' => [
                    30 => RequestAuthenticationProvider::class,
                    50 => CredentialsAuthenticationProvider::class,
                    10 => BasicAuthenticationProvider::class,
                ],
            ],
            '',
            ''
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
     * @throws Exception
     */
    public function getDataProviderTest($url, $expectedClass, $classToBuild = [])
    {
        /** @var ExtractorInterface $extractor */
        $extractor = $this->prophesize(ExtractorInterface::class)->reveal();
        /** @var IdentityProviderInterface $identityProvider */
        $identityProvider = $this->prophesize(IdentityProviderInterface::class)->reveal();
        $this->container->set(RequestFactoryInterface::class, $this->buildRequestFactory($url));
        $this->container->set(
            VirtualObjectDataProvider::class,
            new VirtualObjectDataProvider($this->fixture, $extractor, $identityProvider)
        );
        $this->container->set(DataProvider::class, new DataProvider($this->fixture, $extractor, $identityProvider));
        $this->container->set(DataProviderInterface::class, new DummyDataProvider());

        if ($classToBuild) {
            $this->buildClassAndRegisterObject($classToBuild);
        }

        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf($expectedClass, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
//        $this->assertInstanceOf(DataProvider::class, $dataProvider);
    }

    /**
     * @param string[] $classToBuild
     * @throws Exception
     */
    private function buildClassAndRegisterObject(array $classToBuild): void
    {
        $this->buildClass($classToBuild, '', '', true);
        $className = implode('\\', array_reverse(array_slice($classToBuild, 0, 2)));

        $this->container->set(
            ltrim($className, '\\'),
            function (...$a) use ($className) {
                return new $className(...$a);
            }
        );
    }

    public function dataProviderTestGenerator()
    {
        $dummyDataProvider = DummyDataProvider::class;

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
                ['Tx_MyExt_Rest_DataProvider', '', $dummyDataProvider],
            ],
            [
                'my_ext-my_model/1.json',
                'Tx_MyExt_Rest_DataProvider',
                ['Tx_MyExt_Rest_DataProvider', '', $dummyDataProvider],
            ],
            [
                'MyExt-MyModel/1',
                'Tx_MyExt_Rest_DataProvider',
                ['Tx_MyExt_Rest_DataProvider', '', $dummyDataProvider],
            ],
            [
                'MyExt-MyModel/1.json',
                'Tx_MyExt_Rest_DataProvider',
                ['Tx_MyExt_Rest_DataProvider', '', $dummyDataProvider],
            ],
            [
                'vendor-my_second_ext-my_model/1',
                '\\Vendor\\MySecondExt\\Rest\\DataProvider',
                ['DataProvider', 'Vendor\\MySecondExt\\Rest', $dummyDataProvider],
            ],
            [
                'Vendor-MySecondExt-MyModel/1',
                '\\Vendor\\MySecondExt\\Rest\\DataProvider',
                ['DataProvider', 'Vendor\\MySecondExt\\Rest', $dummyDataProvider],
            ],
            [
                'Vendor-NotExistingExt-MyModel/1',
                $dummyDataProvider,
            ],
            [
                'Vendor-NotExistingExt-MyModel/1.json',
                $dummyDataProvider,
            ],
            [
                'MyThirdExt-MyModel/1.json',
                'Tx_MyThirdExt_Rest_MyModelDataProvider',
                ['Tx_MyThirdExt_Rest_MyModelDataProvider', '', $dummyDataProvider],
            ],
            [
                'Vendor-MySecondExt-MyModel/1.json',
                '\\Vendor\\MySecondExt\\Rest\\MyModelDataProvider',
                ['MyModelDataProvider', 'Vendor\\MySecondExt\\Rest', $dummyDataProvider],
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
     */
    public function getDataProviderFromResourceTest()
    {
        $expectedDataProvider = 'Vendor\\Ext' . time() . '\\Rest\\DataProvider';
        $this->buildClass([$expectedDataProvider, '', DummyDataProvider::class]);
        $this->container->set(
            $expectedDataProvider,
            function () use ($expectedDataProvider) {
                return new $expectedDataProvider();
            }
        );

        $resourceType = new ResourceType('some_extension-my_model');
        $resourceTypeString = (string)$resourceType;
        $configurationProvider = new StandaloneConfigurationProvider([]);
        $configurationProvider->setSettings(
            [
                'paths' => [
                    $resourceTypeString => [
                        'dataProviderClass' => $expectedDataProvider,
                    ],
                ],
            ]
        );
        $this->injectPropertyIntoObject($configurationProvider, 'configurationProvider', $this->fixture);
        $this->container->set(RequestFactoryInterface::class, $this->buildRequestFactory((string)$resourceTypeString));

        $dataProvider = $this->fixture->getDataProvider();
        $this->assertInstanceOf($expectedDataProvider, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
    }

    /**
     * @test
     *
     * @dataProvider handlerTestGenerator
     * @param string $url
     * @param string $expectedClass
     * @param array  $classToBuild
     * @throws Exception
     */
    public function getHandlerTest($url, $expectedClass, $classToBuild = [])
    {
        $this->container->set(RequestFactoryInterface::class, $this->buildRequestFactory($url));
        $this->container->set(
            CrudHandler::class,
            function () {
                /** @var ResponseFactory $responseFactory */
                $responseFactory = $this->prophesize(ResponseFactoryInterface::class)->reveal();
                /** @var LoggerInterface $logger */
                $logger = $this->prophesize(LoggerInterface::class)->reveal();

                return new CrudHandler($this->fixture, $responseFactory, $logger);
            }
        );
        if ($classToBuild) {
            $this->buildClassAndRegisterObject($classToBuild);
        }

        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf($expectedClass, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
//        $this->assertInstanceOf(CrudHandler::class, $handler);
    }

    public function handlerTestGenerator()
    {
        $dummyHandler = DummyHandler::class;

        return [
            // URL,
            // Expected result class,
            // Class to Build
            [
                'my_ext-my_model/1',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $dummyHandler],
            ],
            [
                'my_ext-my_model/1.json',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $dummyHandler],
            ],
            [
                'MyExt-MyModel/1',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $dummyHandler],
            ],
            [
                'MyExt-MyModel/1.json',
                'Tx_MyExt_Rest_Handler',
                ['Tx_MyExt_Rest_Handler', '', $dummyHandler],
            ],
            [
                'vendor-my_second_ext-my_model/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest', $dummyHandler],
            ],
            [
                'Vendor-MySecondExt-MyModel/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest', $dummyHandler],
            ],
            [
                'Vendor-MySecondExt-WhatEver/1',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest', $dummyHandler],
            ],
            [
                'Vendor-MySecondExt-WhatEver/',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest', $dummyHandler],
            ],
            [
                'Vendor-MySecondExt-WhatEver',
                '\\Vendor\\MySecondExt\\Rest\\Handler',
                ['Handler', 'Vendor\\MySecondExt\\Rest', $dummyHandler],
            ],
            [
                'Vendor-NotExistingExt-MyModel/1',
                CrudHandler::class,
            ],
            [
                'Vendor-NotExistingExt-MyModel/1.json',
                CrudHandler::class,
            ],
        ];
    }

    /**
     * @test
     */
    public function getHandlerFromResourceTest()
    {
        $expectedHandler = 'Vendor\\Ext' . time() . '\\Rest\\Handler';
        $this->buildClass([$expectedHandler, '', DummyHandler::class]);
        $this->container->set(
            $expectedHandler,
            function () use ($expectedHandler) {
                return new $expectedHandler();
            }
        );

        $resourceType = new ResourceType('some_extension-my_model');
        $resourceTypeString = (string)$resourceType;
        $configurationProvider = new StandaloneConfigurationProvider([]);
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

        $this->container->set(RequestFactoryInterface::class, $this->buildRequestFactory((string)$resourceTypeString));

        $handler = $this->fixture->getHandler();
        $this->assertInstanceOf($expectedHandler, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }
}
