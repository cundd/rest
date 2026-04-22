<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Configuration\ConfigurationProviderFactory;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\Functional\Integration\FrontendSiteSetupTrait;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;

/**
 * Functional tests for ConfigurationBasedAccessController
 *
 * @see \Cundd\Rest\Tests\Unit\Core\ConfigurationBasedAccessControllerTest for Unit tests
 */
final class ConfigurationBasedAccessControllerTest extends AbstractCase
{
    use FrontendSiteSetupTrait;

    private ConfigurationBasedAccessController $fixture;

    public function setUp(): void
    {
        parent::setUp();
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

        $restObjectManager = $this->getContainer()->get(ObjectManager::class);

        $configurationProviderFactory = $this->prophesize(
            ConfigurationProviderFactory::class
        );
        $configurationProviderFactory
            ->build(Argument::type(RestRequestInterface::class))
            ->willReturn($configurationProvider);

        $this->fixture = new ConfigurationBasedAccessController(
            $configurationProviderFactory->reveal(),
            $restObjectManager
        );
    }

    protected function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function getConfigurationForPathWithoutWildcardTest(): void
    {
        $uri = 'my_ext-my_model/3/';
        $request = $this->buildTestRequestWithSite($uri, 'GET');
        $configuration = $this->fixture->getConfigurationForRequest($request);
        $this->assertSame('my_ext-my_model', (string) $configuration->getResourceType());
        $this->assertTrue(Access::RequireLogin === $configuration->getRead());
        $this->assertTrue(Access::Allowed === $configuration->getWrite());

        $this->assertFalse($this->fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertTrue($this->fixture->requestNeedsAuthentication($request->withMethod('GET')));
        $this->assertFalse(Access::Authorized === $this->fixture->getAccess($request->withMethod('GET')));
        $this->assertTrue(Access::Unauthorized === $this->fixture->getAccess($request->withMethod('GET')));
    }

    #[Test]
    public function getConfigurationForPathWithWildcardTest(): void
    {
        $uri = 'my_secondext-my_model/2/';
        $request = $this->buildTestRequestWithSite($uri, 'GET');
        $configuration = $this->fixture->getConfigurationForRequest($request);
        $this->assertSame('my_secondext-*', (string) $configuration->getResourceType());
        $this->assertTrue(Access::Denied === $configuration->getRead());
        $this->assertTrue(Access::RequireLogin === $configuration->getWrite());

        $this->assertTrue($this->fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertFalse($this->fixture->requestNeedsAuthentication($request->withMethod('GET')));

        $this->assertFalse(Access::Authorized === $this->fixture->getAccess($request->withMethod('POST')));
        $this->assertTrue(Access::Unauthorized === $this->fixture->getAccess($request->withMethod('POST')));
    }

    #[Test]
    public function getDefaultConfigurationForPathTest(): void
    {
        $uri = 'my_ext-my_default_model/1/';
        $request = $this->buildTestRequestWithSite($uri, 'GET');
        $configuration = $this->fixture->getConfigurationForRequest($request);
        $this->assertSame('all', (string) $configuration->getResourceType());
        $this->assertTrue(Access::Allowed === $configuration->getRead());
        $this->assertTrue(Access::Denied === $configuration->getWrite());
    }
}
