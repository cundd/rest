<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\Functional\Fixtures\FrontendUserAuthentication;
use Cundd\Rest\Tests\Functional\Integration\FrontendSiteSetupTrait;

/**
 * Functional tests for ConfigurationBasedAccessController
 *
 * @see \Cundd\Rest\Tests\Unit\Core\ConfigurationBasedAccessControllerTest for Unit tests
 */
class ConfigurationBasedAccessControllerTest extends AbstractCase
{
    use FrontendSiteSetupTrait;

    private ConfigurationBasedAccessController $fixture;

    public function setUp(): void
    {
        parent::setUp();
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

        $this->configureFrontendUserAuthentication();
        $restObjectManager = $this->getContainer()->get(ObjectManager::class);
        $this->fixture = new ConfigurationBasedAccessController($configurationProvider, $restObjectManager);
    }

    protected function tearDown(): void
    {
        unset($this->fixture);
        FrontendUserAuthentication::reset();
        parent::tearDown();
    }

    private function configureFrontendUserAuthentication(): void
    {
        FrontendUserAuthentication::reset();

        $GLOBALS['TSFE'] = (object)['fe_user' => new FrontendUserAuthentication()];
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest()
    {
        $uri = 'my_ext-my_model/3/';
        $request = $this->buildRequestWithUri($uri, null, 'GET');
        $configuration = $this->fixture->getConfigurationForResourceType($request->getResourceType());
        $this->assertSame('my_ext-my_model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isRequireLogin());
        $this->assertTrue($configuration->getWrite()->isAllowed());

        $this->assertFalse($this->fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertTrue($this->fixture->requestNeedsAuthentication($request->withMethod('GET')));
        $this->assertFalse($this->fixture->getAccess($request->withMethod('GET'))->isAuthorized());

        $this->assertFalse($this->fixture->getAccess($request->withMethod('GET'))->isAuthorized());
        $this->assertTrue($this->fixture->getAccess($request->withMethod('GET'))->isUnauthorized());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithWildcardTest()
    {
        $uri = 'my_secondext-my_model/2/';
        $request = $this->buildRequestWithUri($uri, null, 'GET');
        $configuration = $this->fixture->getConfigurationForResourceType($request->getResourceType());
        $this->assertSame('my_secondext-*', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isRequireLogin());

        $this->assertTrue($this->fixture->requestNeedsAuthentication($request->withMethod('POST')));
        $this->assertFalse($this->fixture->requestNeedsAuthentication($request->withMethod('GET')));

        $this->assertFalse($this->fixture->getAccess($request->withMethod('POST'))->isAuthorized());
        $this->assertTrue($this->fixture->getAccess($request->withMethod('POST'))->isUnauthorized());
    }

    /**
     * @test
     */
    public function getDefaultConfigurationForPathTest()
    {
        $uri = 'my_ext-my_default_model/1/';
        $request = $this->buildRequestWithUri($uri, null, 'GET');
        $configuration = $this->fixture->getConfigurationForResourceType($request->getResourceType());
        $this->assertSame('all', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isDenied());
    }
}
