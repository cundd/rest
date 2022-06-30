<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\Authentication\CredentialsAuthenticationProvider;
use Cundd\Rest\Authentication\RequestAuthenticationProvider;
use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Configuration\ConfigurationProvider;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\DataProvider\VirtualObjectDataProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Handler\AuthHandler;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\SessionManager;
use Cundd\Rest\Tests\ClassBuilderTrait;
use Cundd\Rest\Tests\Fixtures\UserProvider;
use Cundd\Rest\Tests\InjectPropertyTrait;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Cundd\Rest\Tests\Unit\Fixtures\Container;
use Cundd\Rest\Tests\Unit\Fixtures\DummyDataProvider;
use Cundd\Rest\Tests\Unit\Fixtures\DummyHandler;
use Cundd\Rest\VirtualObject\ConfigurationFactory;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Unit tests for the ObjectManager
 *
 * @see \Cundd\Rest\Tests\Functional\Core\ObjectManagerTest for Functional tests
 */
class ObjectManagerTest extends TestCase
{
    use ProphecyTrait;
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

    public function setUp(): void
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

    public function tearDown(): void
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
        $this->container->set(AuthenticationProviderCollection::class, new AuthenticationProviderCollection([]));

        $this->injectConfigurationProvider(['authenticationProvider' => []], '', '');
        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('something'));
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
    }

    /**
     * @test
     */
    public function getAuthenticationProviderFromConfigurationTest()
    {
        $userProvider = new UserProvider();
        $sessM = new SessionManager();
        $this->container->set(BasicAuthenticationProvider::class, new BasicAuthenticationProvider($userProvider));
        $this->container->set(RequestAuthenticationProvider::class, new RequestAuthenticationProvider());
        $this->container->set(CredentialsAuthenticationProvider::class, new CredentialsAuthenticationProvider($sessM));
        $this->container->set(
            AuthenticationProviderCollection::class,
            function ($a): AuthenticationProviderCollection {
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
        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('something'));
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
     * @throws Exception
     */
    public function getDataProviderTest(string $url, string $expectedClass)
    {
        /** @var ExtractorInterface $extractor */
        $extractor = $this->prophesize(ExtractorInterface::class)->reveal();
        /** @var IdentityProviderInterface $identityProvider */
        $identityProvider = $this->prophesize(IdentityProviderInterface::class)->reveal();
        $this->container->set(
            VirtualObjectDataProvider::class,
            new VirtualObjectDataProvider(
                new ConfigurationFactory(new ConfigurationProvider()),
                $this->fixture,
                $extractor,
                $identityProvider
            )
        );
        $dataProviderFixture = new DataProvider($this->fixture, $extractor, $identityProvider);
        $this->container->set(DataProvider::class, $dataProviderFixture);
        $this->container->set(DataProviderInterface::class, $dataProviderFixture);

        $dataProvider = $this->fixture->getDataProvider($this->buildTestRequest($url, 'something'));
        $this->assertInstanceOf($expectedClass, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
    }

    public function dataProviderTestGenerator(): array
    {
        return [
            // URL,
            // Expected result class,
            [
                '',
                DataProvider::class,
            ],
            [
                'Vendor-NotExistingExt-MyModel/1',
                DataProvider::class,
            ],
            [
                'Vendor-NotExistingExt-MyModel/1.json',
                DataProvider::class,
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
     * @throws Exception
     */
    public function getDataProviderFromResourceTest()
    {
        $expectedDataProvider = 'Vendor\\Ext' . time() . '\\Rest\\DataProvider';
        $this->buildClass($expectedDataProvider, '', DummyDataProvider::class, true);
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

        $dataProvider = $this->fixture->getDataProvider($this->buildTestRequest($resourceTypeString, 'something'));
        $this->assertInstanceOf($expectedDataProvider, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
    }

    /**
     * @test
     *
     * @dataProvider handlerTestGenerator
     * @param string $url
     * @param string $expectedClass
     * @throws Exception
     */
    public function getHandlerTest(string $url, string $expectedClass)
    {
        $this->container->set(
            CrudHandler::class,
            function (): CrudHandler {
                /** @var ResponseFactory $responseFactory */
                $responseFactory = $this->prophesize(ResponseFactoryInterface::class)->reveal();
                /** @var LoggerInterface $logger */
                $logger = $this->prophesize(LoggerInterface::class)->reveal();

                return new CrudHandler($this->fixture, $responseFactory, $logger);
            }
        );

        $this->container->set(
            AuthHandler::class,
            function (): AuthHandler {
                /** @var SessionManager $sessionManager */
                $sessionManager = $this->prophesize(SessionManager::class)->reveal();
                /** @var UserProviderInterface $userProvider */
                $userProvider = $this->prophesize(UserProviderInterface::class)->reveal();

                return new AuthHandler($sessionManager, $userProvider);
            }
        );

        $handler = $this->fixture->getHandler($this->buildTestRequest($url, 'something'));
        $this->assertInstanceOf($expectedClass, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }

    public function handlerTestGenerator(): array
    {
        return [
            // URL,
            // Expected result class,
            [
                'Vendor-NotExistingExt-MyModel/1',
                CrudHandler::class,
            ],
            [
                'Vendor-NotExistingExt-MyModel/1.json',
                CrudHandler::class,
            ],
            [
                'auth/1.json',
                AuthHandler::class,
            ],
        ];
    }

    /**
     * @test
     */
    public function getHandlerFromResourceTest()
    {
        $expectedHandler = 'Vendor\\Ext' . time() . '\\Rest\\Handler';
        $this->buildClass($expectedHandler, '', DummyHandler::class, true);
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

        $handler = $this->fixture->getHandler($this->buildTestRequest($resourceTypeString, 'something'));
        $this->assertInstanceOf($expectedHandler, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }
}
