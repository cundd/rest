<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Core;


use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Tests\Functional\AbstractCase;

/**
 * Functional tests for ConfigurationBasedAccessController
 *
 * @see \Cundd\Rest\Tests\Unit\Core\ConfigurationBasedAccessController for Unit tests
 */
class ConfigurationBasedAccessControllerTest extends AbstractCase
{
    /**
     * @var ConfigurationBasedAccessController
     */
    private $fixture;

    /**
     * @var ObjectManager
     */
    private $restObjectManager;

    public function setUp()
    {
        parent::setUp();
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

        /** @var ObjectManager $restObjectManager */
        $restObjectManager = $this->objectManager->get(ObjectManager::class);
        $this->fixture = new ConfigurationBasedAccessController($configurationProvider, $restObjectManager);
    }

    protected function tearDown()
    {
        unset($this->fixture);
        unset($this->restObjectManager);
        parent::tearDown();
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
