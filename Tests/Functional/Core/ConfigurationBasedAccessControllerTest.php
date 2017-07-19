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
        $configurationProvider = $this->objectManager->get(
            'Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider'
        );
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
        $testConfiguration = [
            'path'  => 'all',
            'read'  => 'allow',
            'write' => 'deny',
        ];
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest()
    {
        $uri = 'my_ext-my_model/3/';
        $request = $this->buildRequestWithUri($uri);
        $testConfiguration = [
            'path'  => 'my_ext-my_model',
            'read'  => 'allow',
            'write' => 'allow',
        ];
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationTest()
    {
        $uri = 'vendor-my_third_ext-model/3/';
        $request = $this->buildRequestWithUri($uri);
        $testConfiguration = [
            'path'  => 'vendor-my_third_ext-model',
            'read'  => 'deny',
            'write' => 'allow',
        ];
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationWithDotTest()
    {
        $uri = 'vendor-my_fourth_ext-model/3/';
        $request = $this->buildRequestWithUri($uri);
        $testConfiguration = [
            'path'  => 'vendor-my_fourth_ext-model',
            'read'  => 'deny',
            'write' => 'allow',
        ];
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithWildcardTest()
    {
        $testConfiguration = [
            'path'  => 'my_secondext-*',
            'read'  => 'deny',
            'write' => 'allow',
        ];
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType('my_secondext-my_model'));
        $this->assertEquals($testConfiguration, $configuration);
    }
}
