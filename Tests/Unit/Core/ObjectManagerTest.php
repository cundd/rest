<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\Authentication\RequestAuthenticationProvider;
use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\ClassBuilderTrait;
use Cundd\Rest\Tests\Fixtures\UserProvider;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Cundd\Rest\Tests\Unit\Fixtures\Container;
use Cundd\Rest\Tests\Unit\Fixtures\DummyDataProvider;
use Cundd\Rest\Tests\Unit\Fixtures\DummyHandler;
use PHPUnit\Framework\Attributes\DataProvider as PHPUnitDataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Unit tests for the ObjectManager
 *
 * @see \Cundd\Rest\Tests\Functional\Core\ObjectManagerTest for Functional tests
 */
class ObjectManagerTest extends TestCase
{
    use ProphecyTrait;
    use RequestBuilderTrait;
    use ClassBuilderTrait;

    private ObjectManager $fixture;

    private Container $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->fixture = new ObjectManager($this->container);
        $this->injectConfigurationProvider($this->container);
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function getRequestFactoryTest(): void
    {
        /** @var ConfigurationProviderInterface $configurationProvider */
        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class)
            ->reveal();
        $configurationProviderFactory = $this->prophesize(
            ConfigurationProviderFactoryInterface::class
        );
        $configurationProviderFactory
            ->build(Argument::any())
            ->willReturn($configurationProvider);

        $this->container->set(
            RequestFactoryInterface::class,
            new RequestFactory($configurationProviderFactory->reveal())
        );
        $object = $this->fixture->getRequestFactory();
        $this->assertInstanceOf(RequestFactoryInterface::class, $object);
        $this->assertInstanceOf(RequestFactory::class, $object);
    }

    #[Test]
    public function getResponseFactoryTest(): void
    {
        $this->container->set(ResponseFactoryInterface::class, new ResponseFactory());
        $object = $this->fixture->getResponseFactory();
        $this->assertInstanceOf(ResponseFactoryInterface::class, $object);
        $this->assertInstanceOf(ResponseFactory::class, $object);
    }

    #[Test]
    public function getConfigurationProviderTest(): void
    {
        $this->container->set(
            ConfigurationProviderInterface::class,
            new StandaloneConfigurationProvider([])
        );

        $object = $this->fixture->getConfigurationProvider($this->buildTestRequest(''));
        $this->assertInstanceOf(ConfigurationProviderInterface::class, $object);
    }

    #[Test]
    public function getAuthenticationProviderTest(): void
    {
        $this->container->set(
            AuthenticationProviderCollection::class,
            new AuthenticationProviderCollection([])
        );

        $this->injectConfigurationProvider(
            $this->container,
            ['authenticationProvider' => []],
            '',
            ''
        );
        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('something'));
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
    }

    #[Test]
    public function getAuthenticationProviderFromConfigurationTest(): void
    {
        $userProvider = new UserProvider();
        $this->container->set(
            BasicAuthenticationProvider::class,
            new BasicAuthenticationProvider($userProvider)
        );
        $this->container->set(
            RequestAuthenticationProvider::class,
            new RequestAuthenticationProvider(
                $this->prophesize(Context::class)->reveal()
            )
        );
        $this->container->set(
            AuthenticationProviderCollection::class,
            function ($a): AuthenticationProviderCollection {
                return new AuthenticationProviderCollection($a);
            }
        );
        $this->injectConfigurationProvider(
            $this->container,
            [
                'authenticationProvider' => [
                    30 => RequestAuthenticationProvider::class,
                    10 => BasicAuthenticationProvider::class,
                ],
            ],
            '',
            ''
        );

        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('something'));
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
        $this->assertInstanceOf(AuthenticationProviderCollection::class, $object);
        $this->assertCount(2, $object->getProviders());
        $providers = array_values(iterator_to_array($object->getProviders()));
        $this->assertInstanceOf(BasicAuthenticationProvider::class, $providers[0]);
        $this->assertInstanceOf(RequestAuthenticationProvider::class, $providers[1]);
    }

    /**
     * @param class-string $expectedClass
     */
    #[Test]
    #[PHPUnitDataProvider('dataProviderTestGenerator')]
    public function getDataProviderTest(string $url, string $expectedClass): void
    {
        $extractor = $this->prophesize(ExtractorInterface::class)->reveal();
        $identityProvider = $this->prophesize(IdentityProviderInterface::class)->reveal();
        $dataProviderFixture = new DataProvider($this->fixture, $extractor, $identityProvider);
        $this->container->set(DataProvider::class, $dataProviderFixture);
        $this->container->set(DataProviderInterface::class, $dataProviderFixture);

        $dataProvider = $this->fixture->getDataProvider($this->buildTestRequest($url, 'something'));
        $this->assertInstanceOf($expectedClass, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
    }

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function dataProviderTestGenerator(): array
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
        ];
    }

    #[Test]
    public function getDataProviderFromResourceTest(): void
    {
        /** @var class-string<DataProviderInterface> $expectedDataProvider */
        $expectedDataProvider = 'Vendor\\Ext' . time() . '\\Rest\\DataProvider';
        $this->buildClass($expectedDataProvider, '', DummyDataProvider::class, true);
        $this->container->set(
            $expectedDataProvider,
            function () use ($expectedDataProvider) {
                return new $expectedDataProvider();
            }
        );

        $resourceType = new ResourceType('some_extension-my_model');
        $resourceTypeString = (string) $resourceType;
        $configurationProvider = new StandaloneConfigurationProvider([]);
        $settings = [
            'paths' => [
                $resourceTypeString => [
                    'dataProviderClass' => $expectedDataProvider,
                ],
            ],
        ];
        $configurationProvider->setSettings($settings);

        $this->registerConfigurationInstance(
            $this->container,
            $configurationProvider
        );

        $dataProvider = $this->fixture->getDataProvider(
            $this->buildTestRequest($resourceTypeString, 'GET')
        );
        $this->assertInstanceOf($expectedDataProvider, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
    }

    /**
     * @param class-string $expectedClass
     */
    #[Test]
    #[PHPUnitDataProvider('handlerTestGenerator')]
    public function getHandlerTest(string $url, string $expectedClass): void
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

        $handler = $this->fixture->getHandler($this->buildTestRequest($url, 'something'));
        $this->assertInstanceOf($expectedClass, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    public static function handlerTestGenerator(): array
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
        ];
    }

    #[Test]
    public function getHandlerFromResourceTest(): void
    {
        /** @var class-string<HandlerInterface> $expectedHandler */
        $expectedHandler = 'Vendor\\Ext' . time() . '\\Rest\\Handler';
        $this->buildClass($expectedHandler, '', DummyHandler::class, true);
        $this->container->set(
            $expectedHandler,
            function () use ($expectedHandler) {
                return new $expectedHandler();
            }
        );

        $resourceType = new ResourceType('some_extension-my_model');
        $resourceTypeString = (string) $resourceType;
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
        $this->registerConfigurationInstance(
            $this->container,
            $configurationProvider
        );

        $handler = $this->fixture->getHandler($this->buildTestRequest($resourceTypeString, 'GET'));
        $this->assertInstanceOf($expectedHandler, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }

    /**
     * @param array<string,mixed> $settings
     */
    private function injectConfigurationProvider(
        Container $container,
        array $settings = [],
        string $handler = '',
        string $dataProvider = '',
    ): void {
        $resourceConfiguration = $this->prophesize(ResourceConfiguration::class);
        $handlerClassMethod = $resourceConfiguration->getHandlerClass();
        $handlerClassMethod->willReturn($handler);

        $dataProviderClassMethod = $resourceConfiguration->getDataProviderClass();
        $dataProviderClassMethod->willReturn($dataProvider);

        $configurationProvider = $this->prophesize(ConfigurationProviderInterface::class);
        /** @var ResourceType $resourceType */
        $resourceType = Argument::any();
        $methodProphecy = $configurationProvider->getResourceConfiguration($resourceType);
        $methodProphecy
            ->willReturn($resourceConfiguration->reveal());

        /** @var string $typeToken */
        $typeToken = Argument::type('string');
        /** @var MethodProphecy $getSettingsProphecy */
        $getSettingsProphecy = $configurationProvider->getSetting($typeToken);
        $getSettingsProphecy->will(fn ($args) => $settings[$args[0]] ?? null);

        $configurationProvider = $configurationProvider->reveal();

        $this->registerConfigurationInstance($container, $configurationProvider);
    }

    private function registerConfigurationInstance(
        Container $container,
        ConfigurationProviderInterface $configurationProvider,
    ): void {
        $container->set(ConfigurationProviderFactoryInterface::class, new class($configurationProvider) implements ConfigurationProviderFactoryInterface {
            public function __construct(private readonly ConfigurationProviderInterface $configurationProvider)
            {
            }

            public function build(
                ServerRequestInterface $request,
            ): ConfigurationProviderInterface {
                return $this->configurationProvider;
            }

            public function buildFromSite(Site $site): ConfigurationProviderInterface
            {
                return $this->configurationProvider;
            }
        });
    }
}
