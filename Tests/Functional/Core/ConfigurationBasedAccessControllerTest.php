<?php

namespace Cundd\Rest\Tests\Functional\Core;


use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Tests\Functional\AbstractCase;

class ConfigurationBasedAccessControllerTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\Access\ConfigurationBasedAccessController
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        /** @var TypoScriptConfigurationProvider $configurationProvider */
        $configurationProvider = $this->objectManager->get(TypoScriptConfigurationProvider::class);
        $settings = [
            'paths' => [
                'all'                         => [
                    'path'  => 'all',
                    'read'  => 'allow',
                    'write' => 'deny',
                ],
                'my_ext-my_model'             => [
                    'path'  => 'my_ext-my_model',
                    'read'  => 'allow',
                    'write' => 'allow',
                ],
                'my_secondext-*'              => [
                    'path'  => 'my_secondext-*',
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
                'vendor-my_third_ext-model'   => [
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
                'vendor-my_fourth_ext-model.' => [
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
            ],
        ];
        $configurationProvider->setSettings($settings);

        /** @var ObjectManager $restObjectManager */
        $restObjectManager = $this->objectManager->get(ObjectManager::class);
        $this->fixture = new ConfigurationBasedAccessController($configurationProvider, $restObjectManager);
    }

    /**
     * @test
     */
    public function getDefaultConfigurationForPathTest()
    {
        $uri = 'my_ext-my_default_model/1/';
        $request = $this->buildRequestWithUri($uri);
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertSame('all', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isDenied());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest()
    {
        $uri = 'my_ext-my_model/3/';
        $request = $this->buildRequestWithUri($uri);
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertSame('my_ext-my_model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationTest()
    {
        $uri = 'vendor-my_third_ext-model/3/';
        $request = $this->buildRequestWithUri($uri);
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertSame('vendor-my_third_ext-model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationWithDotTest()
    {
        $uri = 'vendor-my_fourth_ext-model/3/';
        $request = $this->buildRequestWithUri($uri);
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertSame('vendor-my_fourth_ext-model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithWildcardTest()
    {
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType('my_secondext-my_model'));
        $this->assertSame('my_secondext-*', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }
}
