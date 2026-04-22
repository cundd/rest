<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Request;
use Cundd\Rest\Tests\Functional\Fixtures\DummyAuthenticationProvider;
use Cundd\Rest\Tests\RequestBuilderTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Unit tests for ConfigurationBasedAccessController
 *
 * @see \Cundd\Rest\Tests\Functional\Core\ConfigurationBasedAccessControllerTest for Functional tests
 */
final class ConfigurationBasedAccessControllerTest extends TestCase
{
    use ProphecyTrait;
    use RequestBuilderTrait;

    #[Test]
    public function getConfigurationForPathWithoutWildcardTest(): void
    {
        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var Request $type */
                $type = Argument::type(Request::class);

                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider($type);
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(true));
            }
        );

        $uri = 'my_ext-my_model/3/';
        $request = $this->buildTestRequest($uri, 'GET');
        $configuration = $fixture->getConfigurationForRequest($request);
        $this->assertSame('my_ext-my_model', (string) $configuration->getResourceType());
        $this->assertTrue(Access::RequireLogin === $configuration->getRead());
        $this->assertTrue(Access::Allowed === $configuration->getWrite());

        $this->assertFalse($fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertTrue($fixture->requestNeedsAuthentication($request->withMethod('GET')));
        $this->assertTrue(Access::Authorized === $fixture->getAccess($request->withMethod('GET')));

        $fixture2 = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var Request $type */
                $type = Argument::type(Request::class);

                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider($type);
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(true));
            }
        );
        $this->assertTrue(Access::Authorized === $fixture2->getAccess($request->withMethod('GET')));

        $fixture3 = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var Request $type */
                $type = Argument::type(Request::class);

                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider($type);
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $this->assertFalse(Access::Authorized === $fixture3->getAccess($request->withMethod('GET')));
        $this->assertTrue(Access::Unauthorized === $fixture3->getAccess($request->withMethod('GET')));
    }

    private function buildAccessController(
        ?callable $configureObjectManager = null,
    ): ConfigurationBasedAccessController {
        $configurationProvider = new StandaloneConfigurationProvider(
            [
                'paths' => [
                    'all' => [
                        'path'  => 'all',
                        'read'  => 'allow',
                        'write' => 'deny',
                    ],
                    'my_ext-my_model' => [
                        'path'  => 'my_ext-my_model',
                        'read'  => 'require',
                        'write' => 'allow',
                    ],
                    'my_secondext-*' => [
                        'path'  => 'my_secondext-*',
                        'read'  => 'deny',
                        'write' => 'require',
                    ],
                ],
            ]
        );

        /** @var LoggerInterface $logger */
        $logger = $this->prophesize(LoggerInterface::class)->reveal();

        $objectManagerProphecy = $this->prophesize(ObjectManagerInterface::class);
        $objectManagerProphecy->get(LoggerInterface::class)->willReturn($logger);
        if ($configureObjectManager) {
            $configureObjectManager($objectManagerProphecy);
        }
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProphecy->reveal();

        $configurationProviderFactory = new class($configurationProvider) implements ConfigurationProviderFactoryInterface {
            public function __construct(
                private readonly ConfigurationProviderInterface $configurationProvider,
            ) {
            }

            public function build(
                ServerRequestInterface $request,
            ): ConfigurationProviderInterface {
                return $this->configurationProvider;
            }

            public function buildFromSite(
                Site $site,
            ): ConfigurationProviderInterface {
                return $this->configurationProvider;
            }
        };

        return new ConfigurationBasedAccessController($configurationProviderFactory, $objectManager);
    }

    #[Test]
    public function getConfigurationForPathWithWildcardTest(): void
    {
        $uri = 'my_secondext-my_model/2/';
        $request = $this->buildTestRequest($uri, 'GET');
        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var Request $type */
                $type = Argument::type(Request::class);

                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider($type);
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $configuration = $fixture->getConfigurationForRequest($request);
        $this->assertSame('my_secondext-*', (string) $configuration->getResourceType());
        $this->assertTrue(Access::Denied === $configuration->getRead());
        $this->assertTrue(Access::RequireLogin === $configuration->getWrite());

        $this->assertTrue($fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertFalse($fixture->requestNeedsAuthentication($request->withMethod('GET')));

        $fixture2 = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var Request $type */
                $type = Argument::type(Request::class);

                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider($type);
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(true));
            }
        );
        $this->assertTrue(Access::Authorized === $fixture2->getAccess($request->withMethod('POST')));

        $fixture3 = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var Request $type */
                $type = Argument::type(Request::class);

                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider($type);
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $this->assertFalse(Access::Authorized === $fixture3->getAccess($request->withMethod('POST')));
        $this->assertTrue(Access::Unauthorized === $fixture3->getAccess($request->withMethod('POST')));
    }

    #[Test]
    public function getDefaultConfigurationForPathTest(): void
    {
        $fixture = $this->buildAccessController(
            function ($om) {
                /** @var ObjectProphecy|ObjectManagerInterface $om */
                /** @var Request $type */
                $type = Argument::type(Request::class);

                /** @var MethodProphecy $authenticationProviderMethod */
                $authenticationProviderMethod = $om->getAuthenticationProvider($type);
                $authenticationProviderMethod->willReturn(new DummyAuthenticationProvider(false));
            }
        );
        $uri = 'my_ext-my_default_model/1/';
        $request = $this->buildTestRequest($uri, 'GET');
        $configuration = $fixture->getConfigurationForRequest($request);
        $this->assertSame('all', (string) $configuration->getResourceType());
        $this->assertTrue(Access::Allowed === $configuration->getRead());
        $this->assertTrue(Access::Denied === $configuration->getWrite());
    }
}
