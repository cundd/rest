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
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\VirtualObjectDataProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Handler\AuthHandler;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\ResponseFactory;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Exception;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Functional tests for the ObjectManager
 *
 * @see \Cundd\Rest\Tests\Unit\Core\ObjectManagerTest for Unit tests
 */
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
        $this->injectConfigurationProviderUsingHandlerClass();
    }

    public function tearDown()
    {
        // Reset the last request
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
        $this->injectConfigurationProviderUsingHandlerClass(['authenticationProvider' => []], '');
        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('/something'));
        $this->assertInstanceOf(AuthenticationProviderInterface::class, $object);
    }

    /**
     * @test
     */
    public function getAuthenticationProviderFromConfigurationTest()
    {
        $this->injectConfigurationProviderUsingHandlerClass(
            [
                'authenticationProvider' => [
                    30 => RequestAuthenticationProvider::class,
                    50 => CredentialsAuthenticationProvider::class,
                    10 => BasicAuthenticationProvider::class,
                ],
            ],
            ''
        );
        /** @var AuthenticationProviderCollection $object */
        $object = $this->fixture->getAuthenticationProvider($this->buildTestRequest('/something'));
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
    public function getDataProviderTest(string $url, string $expectedClass, $classToBuild = [])
    {
        $_GET['u'] = $url;
        if ($classToBuild) {
            $this->buildClass($classToBuild);
        }

        $dataProvider = $this->fixture->getDataProvider($this->buildTestRequest($url));
        $this->assertInstanceOf($expectedClass, $dataProvider);
        $this->assertInstanceOf(DataProviderInterface::class, $dataProvider);
        $this->assertInstanceOf(DataProvider::class, $dataProvider);
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
     *
     * @dataProvider handlerTestGenerator
     * @param string $url
     * @param string $expectedClass
     * @param array  $classToBuild
     * @throws Exception
     */
    public function getHandlerTest(string $url, string $expectedClass, $classToBuild = [])
    {
        $_GET['u'] = $url;
        if ($classToBuild) {
            $this->buildClass($classToBuild);
        }

        $handler = $this->fixture->getHandler($this->buildTestRequest($url));
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
        $expectedHandler = '\\Vendor\\Ext' . time() . '\\Rest\\Handler';
        $this->buildClass([$expectedHandler, '', CrudHandler::class]);

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

        $_GET['u'] = $resourceTypeString;

        $handler = $this->fixture->getHandler($this->buildTestRequest($resourceTypeString));
        $this->assertInstanceOf($expectedHandler, $handler);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertInstanceOf(CrudHandler::class, $handler);
    }

    /**
     * @param array  $settings
     * @param string $handler
     * @param string $dataProvider
     */
    private function injectConfigurationProviderUsingHandlerClass(
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
}
