<?php

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;

class TypoScriptConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypoScriptConfigurationProvider
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = new TypoScriptConfigurationProvider();
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
}
