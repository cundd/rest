<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Authentication\AuthenticationProviderCollection;
use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\Authentication\RequestAuthenticationProvider;
use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\SiteSettingsConfigurationProvider;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use PHPUnit\Framework\Attributes\DataProvider as PHPUnitDataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Functional tests for the ObjectManager
 *
 * @see \Cundd\Rest\Tests\Unit\Core\ObjectManagerTest for Unit tests
 */
final class ObjectManagerTest extends AbstractCase
{
    protected ObjectManager $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $container = $this->getContainer();
        assert($container instanceof Container);
        $this->fixture = new ObjectManager($container);
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function getRequestFactoryTest(): void
    {
        $container = $this->getContainer();
        assert($container instanceof Container);
        $this->injectConfigurationProvider($container);
        $object = $this->fixture->getRequestFactory();
        $this->assertInstanceOf(RequestFactoryInterface::class, $object);
        $this->assertInstanceOf(RequestFactory::class, $object);
    }

    #[Test]
    public function getResponseFactoryTest(): void
    {
        $container = $this->getContainer();
        assert($container instanceof Container);
        $this->injectConfigurationProvider($container);
        $object = $this->fixture->getResponseFactory();
        $this->assertInstanceOf(ResponseFactoryInterface::class, $object);
        $this->assertInstanceOf(ResponseFactory::class, $object);
    }

    #[Test]
    public function getConfigurationProviderTest(): void
    {
        $this->fixture = new ObjectManager();
        $object = $this->fixture->getConfigurationProvider(
            $this->buildTestRequestWithSite('/')
        );
        $this->assertInstanceOf(SiteSettingsConfigurationProvider::class, $object);
    }

    #[Test]
    public function getAuthenticationProviderTest(): void
    {
        $container = $this->getContainer();
        assert($container instanceof Container);
        $this->injectConfigurationProvider(
            $container,
            ['authenticationProvider' => []]
        );
        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('/something'));
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
    }

    #[Test]
    public function getAuthenticationProviderFromConfigurationTest(): void
    {
        $container = $this->getContainer();
        assert($container instanceof Container);
        $this->injectConfigurationProvider(
            $container,
            [
                'authenticationProvider' => [
                    30 => RequestAuthenticationProvider::class,
                    10 => BasicAuthenticationProvider::class,
                ],
            ]
        );
        /** @var AuthenticationProviderCollection $object */
        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('/something'));
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
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
        $container = $this->getContainer();
        assert($container instanceof Container);
        $this->injectConfigurationProvider($container);

        $dataProvider = $this->fixture->getDataProvider($this->buildTestRequest($url));
        $this->assertInstanceOf($expectedClass, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
        $this->assertInstanceOf(DataProvider::class, $dataProvider);
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

    /**
     * @param class-string $expectedClass
     */
    #[Test]
    #[PHPUnitDataProvider('handlerTestGenerator')]
    public function getHandlerTest(string $url, string $expectedClass): void
    {
        $container = $this->getContainer();

        assert($container instanceof Container);
        $this->injectConfigurationProvider($container);

        $handler = $this->fixture->getHandler($this->buildTestRequest($url));
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
        $this->buildClass($expectedHandler, '', CrudHandler::class);

        $resourceType = new ResourceType('some_extension-my_model' . time());

        $container = $this->getContainer();
        assert($container instanceof Container);
        $container->set(
            $expectedHandler,
            new $expectedHandler(
                $this->fixture,
                new ResponseFactory(),
                $this->getContainer()->get(LoggerInterface::class)
            )
        );
        $this->injectConfigurationProvider($container, [], $expectedHandler);

        $handler = $this->fixture->getHandler(
            $this->buildTestRequestWithSite((string) $resourceType)
        );
        $this->assertInstanceOf($expectedHandler, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertInstanceOf(CrudHandler::class, $handler);
    }

    /**
     * @param array<string,mixed>                   $settings
     * @param class-string<HandlerInterface>|string $handlerClass
     */
    private function injectConfigurationProvider(
        Container $container,
        array $settings = [],
        string $handlerClass = '',
    ): void {
        $resourceConfiguration = $this->prophesize(ResourceConfiguration::class);
        $handlerClassMethod = $resourceConfiguration->getHandlerClass();
        $handlerClassMethod->willReturn($handlerClass);

        $dataProviderClassMethod = $resourceConfiguration->getDataProviderClass();
        $dataProviderClassMethod->willReturn('');

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

        $container->set(
            ConfigurationProviderFactoryInterface::class,
            new class($configurationProvider->reveal()) implements ConfigurationProviderFactoryInterface {
                public function __construct(
                    private readonly ConfigurationProviderInterface $configurationProvider,
                ) {
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
            }
        );
    }
}
