<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Handler\CrudHandler;
use PHPUnit\Framework\TestCase;

abstract class AbstractConfigurationProviderCase extends TestCase
{
    /**
     * @var ConfigurationProviderInterface|TypoScriptConfigurationProvider|StandaloneConfigurationProvider
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        $settings = [
            'paths' => [
                'all'                         => [
                    'path'         => 'all',
                    'read'         => 'allow',
                    'write'        => 'deny',
                    'handlerClass' => CrudHandler::class,
                ],
                'my_ext-my_model'             => [
                    'path'         => 'my_ext-my_model',
                    'read'         => 'allow',
                    'write'        => 'allow',
                    'handlerClass' => 'SomeClass2',
                ],
                'my_secondext-*'              => [
                    'path'         => 'my_secondext-*',
                    'read'         => 'deny',
                    'write'        => 'allow',
                    'handlerClass' => 'SomeClass3',
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
        $this->fixture = $this->getConfigurationProviderToTest();
        $this->fixture->setSettings($settings);
    }

    /**
     * @return ConfigurationProviderInterface|TypoScriptConfigurationProvider|StandaloneConfigurationProvider
     */
    abstract function getConfigurationProviderToTest();

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
                    'vendor-my_other_ext-my_model2' => [
                        'path'          => 'vendor-my_other_ext-my_model2',
                        'cacheLifetime' => 3,
                    ],
                ],
            ]
        );

        $resourceTypeConfigurations = $this->fixture->getConfiguredResources();
        $this->assertInternalType('array', $resourceTypeConfigurations);
        $this->assertCount(4, $resourceTypeConfigurations);
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
        $this->assertSame(-1, $resourceConfiguration1->getCacheLifetime());

        $resourceConfiguration2 = $resourceTypeConfigurations['vendor-my_ext-my_model'];
        $this->assertSame('vendor-my_ext-my_model', (string)$resourceConfiguration2->getResourceType());
        $this->assertTrue($resourceConfiguration2->getRead()->isRequireLogin());
        $this->assertTrue($resourceConfiguration2->getWrite()->isDenied());
        $this->assertSame(-1, $resourceConfiguration2->getCacheLifetime());

        $resourceConfiguration3 = $resourceTypeConfigurations['vendor-my_other_ext-my_model'];
        $this->assertSame('vendor-my_other_ext-my_model', (string)$resourceConfiguration3->getResourceType());
        $this->assertTrue($resourceConfiguration3->getRead()->isDenied());
        $this->assertTrue($resourceConfiguration3->getWrite()->isDenied());
        $this->assertSame(2, $resourceConfiguration3->getCacheLifetime());

        $resourceConfiguration4 = $resourceTypeConfigurations['vendor-my_other_ext-my_model2'];
        $this->assertSame('vendor-my_other_ext-my_model2', (string)$resourceConfiguration4->getResourceType());
        $this->assertTrue($resourceConfiguration4->getRead()->isDenied());
        $this->assertTrue($resourceConfiguration4->getWrite()->isDenied());
        $this->assertSame(3, $resourceConfiguration4->getCacheLifetime());
    }

    /**
     * @test
     * @expectedException \Cundd\Rest\Exception\InvalidArgumentException
     */
    public function getConfiguredResourceTypesInvalidReadTest()
    {
        $this->fixture->setSettings(['paths' => ['my_protectedext' => ['read' => 'invalid']]]);
        $this->fixture->getConfiguredResources();
    }

    /**
     * @test
     * @expectedException \Cundd\Rest\Exception\InvalidArgumentException
     */
    public function getConfiguredResourceTypesInvalidWriteTest()
    {
        $this->fixture->setSettings(['paths' => ['my_protectedext' => ['write' => 'invalid']]]);
        $this->fixture->getConfiguredResources();
    }

    /**
     * @test
     */
    public function getDefaultConfigurationForPathTest()
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_ext-my_default_model'));
        $this->assertSame('all', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isDenied());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest()
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_ext-my_model'));
        $this->assertSame('my_ext-my_model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isAllowed());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationTest()
    {
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('vendor-my_third_ext-model'));
        $this->assertSame('vendor-my_third_ext-model', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationWithDotTest()
    {
        $configuration = $this->fixture->getResourceConfiguration(
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
        $configuration = $this->fixture->getResourceConfiguration(new ResourceType('my_secondext-my_model'));
        $this->assertSame('my_secondext-*', (string)$configuration->getResourceType());
        $this->assertTrue($configuration->getRead()->isDenied());
        $this->assertTrue($configuration->getWrite()->isAllowed());
    }

    /**
     * @test
     */
    public function getConfiguredHandlersTest()
    {
        $handlerConfigurations = $this->fixture->getConfiguredResources();
        $this->assertInternalType('array', $handlerConfigurations);
        $this->assertCount(5, $handlerConfigurations);
        array_map(
            function ($c) {
                $this->assertInstanceOf(ResourceConfiguration::class, $c);
            },
            $handlerConfigurations
        );

        $handlerConfiguration1 = $handlerConfigurations['all'];
        $this->assertSame('all', (string)$handlerConfiguration1->getResourceType());
        $this->assertSame(CrudHandler::class, $handlerConfiguration1->getHandlerClass());

        $handlerConfiguration2 = $handlerConfigurations['my_ext-my_model'];
        $this->assertSame('my_ext-my_model', (string)$handlerConfiguration2->getResourceType());
        $this->assertSame('SomeClass2', $handlerConfiguration2->getHandlerClass());

        $handlerConfiguration3 = $handlerConfigurations['vendor-my_third_ext-model'];
        $this->assertSame('vendor-my_third_ext-model', (string)$handlerConfiguration3->getResourceType());
        $this->assertSame('', $handlerConfiguration3->getHandlerClass());
    }
}
