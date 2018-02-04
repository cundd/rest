<?php

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\HandlerConfiguration;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Domain\Model\ResourceType;

class TypoScriptConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypoScriptConfigurationProvider
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

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
        $this->fixture = new TypoScriptConfigurationProvider();
        $this->fixture->setSettings($settings);
    }

    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function getConfiguredResourceTypesTest()
    {
        $this->fixture->setSettings(
            [
                'paths' => [
                    'my_protectedext'              => [
                        'path'  => 'my_protectedext-*',
                        'read'  => 'allow',
                        'write' => 'require',
                    ],
                    'vendor-my_ext-my_model'       => [
                        'read'  => 'require',
                        'write' => 'deny',
                    ],
                    'vendor-my_other_ext-my_model' => [
                        'path'          => 'vendor-my_other_ext-my_model',
                        'cacheLifeTime' => 2,
                    ],
                ],
            ]
        );

        $resourceTypeConfigurations = $this->fixture->getConfiguredResourceTypes();
        $this->assertInternalType('array', $resourceTypeConfigurations);
        $this->assertCount(3, $resourceTypeConfigurations);
        array_map(
            function ($c) {
                $this->assertInstanceOf(ResourceConfiguration::class, $c);
            },
            $resourceTypeConfigurations
        );

        $resourceConfiguration1 = $resourceTypeConfigurations['my_protectedext-*'];
        $this->assertSame('my_protectedext-*', (string)$resourceConfiguration1->getResourceType());
        $this->assertTrue($resourceConfiguration1->getRead()->isAllowed());
        $this->assertTrue($resourceConfiguration1->getWrite()->isRequireLogin());
        $this->assertSame(-1, $resourceConfiguration1->getCacheLiveTime());

        $resourceConfiguration2 = $resourceTypeConfigurations['vendor-my_ext-my_model'];
        $this->assertSame('vendor-my_ext-my_model', (string)$resourceConfiguration2->getResourceType());
        $this->assertTrue($resourceConfiguration2->getRead()->isRequireLogin());
        $this->assertTrue($resourceConfiguration2->getWrite()->isDenied());
        $this->assertSame(-1, $resourceConfiguration2->getCacheLiveTime());

        $resourceConfiguration3 = $resourceTypeConfigurations['vendor-my_other_ext-my_model'];
        $this->assertSame('vendor-my_other_ext-my_model', (string)$resourceConfiguration3->getResourceType());
        $this->assertTrue($resourceConfiguration3->getRead()->isDenied());
        $this->assertTrue($resourceConfiguration3->getWrite()->isDenied());
        $this->assertSame(2, $resourceConfiguration3->getCacheLiveTime());
    }

    /**
     * @test
     * @expectedException \Cundd\Rest\Exception\InvalidArgumentException
     */
    public function getConfiguredResourceTypesInvalidReadTest()
    {
        $this->fixture->setSettings(['paths' => ['my_protectedext' => ['read' => 'invalid']]]);
        $this->fixture->getConfiguredResourceTypes();
    }

    /**
     * @test
     * @expectedException \Cundd\Rest\Exception\InvalidArgumentException
     */
    public function getConfiguredResourceTypesInvalidWriteTest()
    {
        $this->fixture->setSettings(['paths' => ['my_protectedext' => ['write' => 'invalid']]]);
        $this->fixture->getConfiguredResourceTypes();
    }

    /**
     * @test
     */
    public function getDefaultConfigurationForPathTest()
    {
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType('my_ext-my_default_model'));
        $this->assertSame('all', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isDenied());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest()
    {
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType('my_ext-my_model'));
        $this->assertSame('my_ext-my_model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationTest()
    {
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType('vendor-my_third_ext-model'));
        $this->assertSame('vendor-my_third_ext-model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationWithDotTest()
    {
        $configuration = $this->fixture->getConfigurationForResourceType(
            new ResourceType('vendor-my_fourth_ext-model')
        );
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


    /**
     * @test
     */
    public function getConfiguredHandlersTest()
    {
        $this->fixture->setSettings(
            [
                'handler' => [
                    'my_protectedext'              => [
                        'path'      => 'my_protectedext-*',
                        'className' => 'SomeClass1',
                    ],
                    'vendor-my_ext-my_model'       => [
                        'className' => 'SomeClass2',
                    ],
                    'vendor-my_other_ext-my_model' => [
                        'path'      => 'vendor-my_other_ext-my_model',
                        'className' => 'SomeClass3',
                    ],
                ],
            ]
        );

        $handlerConfigurations = $this->fixture->getConfiguredHandlers();
        $this->assertInternalType('array', $handlerConfigurations);
        $this->assertCount(3, $handlerConfigurations);
        array_map(
            function ($c) {
                $this->assertInstanceOf(HandlerConfiguration::class, $c);
            },
            $handlerConfigurations
        );

        $handlerConfiguration1 = $handlerConfigurations['my_protectedext-*'];
        $this->assertSame('my_protectedext-*', (string)$handlerConfiguration1->getResourceType());
        $this->assertSame('SomeClass1', $handlerConfiguration1->getClassName());

        $handlerConfiguration2 = $handlerConfigurations['vendor-my_ext-my_model'];
        $this->assertSame('vendor-my_ext-my_model', (string)$handlerConfiguration2->getResourceType());
        $this->assertSame('SomeClass2', $handlerConfiguration2->getClassName());

        $handlerConfiguration3 = $handlerConfigurations['vendor-my_other_ext-my_model'];
        $this->assertSame('vendor-my_other_ext-my_model', (string)$handlerConfiguration3->getResourceType());
        $this->assertSame('SomeClass3', $handlerConfiguration3->getClassName());
    }
}
