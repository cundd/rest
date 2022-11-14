<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Request;
use Cundd\Rest\Tests\Functional\Fixtures\DummyAuthenticationProvider;
use Cundd\Rest\Tests\RequestBuilderTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Unit tests for ConfigurationBasedAccessController
 *
 * @see \Cundd\Rest\Tests\Functional\Core\ConfigurationBasedAccessController for Functional tests
 */
class ConfigurationBasedAccessControllerTest extends TestCase
{
    use ProphecyTrait;
    use RequestBuilderTrait;

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest()
    {
        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider(Argument::type(Request::class));
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(true));
            }
        );

        $uri = 'my_ext-my_model/3/';
        $request = $this->buildTestRequest($uri, 'GET');
        $configuration = $fixture->getConfigurationForResourceType($request->getResourceType());
        $this->assertSame('my_ext-my_model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isRequireLogin());
        $this->assertTrue($configuration->getWrite()->isAllowed());

        $this->assertFalse($fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertTrue($fixture->requestNeedsAuthentication($request->withMethod('GET')));
        $this->assertTrue($fixture->getAccess($request->withMethod('GET'))->isAuthorized());

        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider(Argument::type(Request::class));
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(true));
            }
        );
        $this->assertTrue($fixture->getAccess($request->withMethod('GET'))->isAuthorized());
        $this->assertFalse($fixture->getAccess($request->withMethod('GET'))->isUnauthorized());

        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider(Argument::type(Request::class));
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $this->assertFalse($fixture->getAccess($request->withMethod('GET'))->isAuthorized());
        $this->assertTrue($fixture->getAccess($request->withMethod('GET'))->isUnauthorized());
    }

    private function buildAccessController(callable $configureObjectManager = null): ConfigurationBasedAccessController
    {
        /** @var TypoScriptConfigurationProvider $configurationProvider */
        $configurationProvider = new StandaloneConfigurationProvider(
            [
                'paths' => [
                    'all'             => [
                        'path'  => 'all',
                        'read'  => 'allow',
                        'write' => 'deny',
                    ],
                    'my_ext-my_model' => [
                        'path'  => 'my_ext-my_model',
                        'read'  => 'require',
                        'write' => 'allow',
                    ],
                    'my_secondext-*'  => [
                        'path'  => 'my_secondext-*',
                        'read'  => 'deny',
                        'write' => 'require',
                    ],
                ],
            ]
        );

        /** @var LoggerInterface $logger */
        $logger = $this->prophesize(LoggerInterface::class)->reveal();

        /** @var ObjectProphecy|ObjectManagerInterface $objectManagerProphecy */
        $objectManagerProphecy = $this->prophesize(ObjectManagerInterface::class);
        $objectManagerProphecy->get(LoggerInterface::class)->willReturn($logger);
        if ($configureObjectManager) {
            $configureObjectManager($objectManagerProphecy);
        }
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProphecy->reveal();

        return new ConfigurationBasedAccessController($configurationProvider, $objectManager);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithWildcardTest()
    {
        $uri = 'my_secondext-my_model/2/';
        $request = $this->buildTestRequest($uri, 'GET');
        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider(Argument::type(Request::class));
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $configuration = $fixture->getConfigurationForResourceType($request->getResourceType());
        $this->assertSame('my_secondext-*', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isRequireLogin());

        $this->assertTrue($fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertFalse($fixture->requestNeedsAuthentication($request->withMethod('GET')));

        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider(Argument::type(Request::class));
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(true));
            }
        );
        $this->assertTrue($fixture->getAccess($request->withMethod('POST'))->isAuthorized());
        $this->assertFalse($fixture->getAccess($request->withMethod('POST'))->isUnauthorized());

        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider(Argument::type(Request::class));
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $this->assertFalse($fixture->getAccess($request->withMethod('POST'))->isAuthorized());
        $this->assertTrue($fixture->getAccess($request->withMethod('POST'))->isUnauthorized());
    }

    /**
     * @test
     */
    public function getDefaultConfigurationForPathTest()
    {
        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider(Argument::type(Request::class));
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $uri = 'my_ext-my_default_model/1/';
        $request = $this->buildTestRequest($uri, 'GET');
        $configuration = $fixture->getConfigurationForResourceType($request->getResourceType());
        $this->assertSame('all', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isDenied());
    }
}
