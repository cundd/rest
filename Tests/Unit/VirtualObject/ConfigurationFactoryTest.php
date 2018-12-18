<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\VirtualObject;

use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Tests\Unit\VirtualObjectCaseTrait;
use Cundd\Rest\VirtualObject\ConfigurationFactory;
use Cundd\Rest\VirtualObject\ConfigurationInterface;

/**
 * Class ConfigurationTest
 */
class ConfigurationFactoryTest extends \PHPUnit\Framework\TestCase
{
    use VirtualObjectCaseTrait;

    /**
     * @var \Cundd\Rest\VirtualObject\ConfigurationFactory
     */
    protected $fixture;

    protected $typoScriptDummyArray = [
        'virtualObjects' => [
            'resource_type' => [
                'mapping.' => [
                    'identifier'  => 'property1',
                    'tableName'   => 'my_resource_table',
                    'properties.' => [
                        'property1.'      => [
                            'type'   => 'string',
                            'column' => 'property_one',
                        ],
                        'property2.'      => [
                            'type'   => 'float',
                            'column' => 'property_two',
                        ],
                        'property3.'      => [
                            'type'   => 'int',
                            'column' => 'property_three',
                        ],
                        'property4.'      => [
                            'type'   => 'integer',
                            'column' => 'property_four',
                        ],
                        'property5.'      => [
                            'type'   => 'bool',
                            'column' => 'property_five',
                        ],
                        'property6.'      => [
                            'type'   => 'boolean',
                            'column' => 'property_six',
                        ],
                        'property_seven.' => [
                            'type' => 'boolean',
                        ],
                        'property_eight.' => 'boolean',
                    ],
                ],
            ],
        ],
    ];


    public function setUp()
    {
        parent::setUp();
        $this->fixture = new ConfigurationFactory(new StandaloneConfigurationProvider($this->typoScriptDummyArray));
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function createTest()
    {
        $this->assertInstanceOf(ConfigurationInterface::class, $this->fixture->create());
    }

    /**
     * @test
     */
    public function createFromArrayTest()
    {
        $configurationObject = $this->fixture->createFromArrayForResourceType(
            $this->getTestConfigurationData(),
            new ResourceType('ResourceType')
        );
        $this->validateConfiguration($configurationObject);
    }

    /**
     * @test
     */
    public function createWithConfigurationDataTest()
    {
        $configurationData = $this->typoScriptDummyArray['virtualObjects']['resource_type']['mapping.'];
        $configurationObject = $this->fixture->createWithConfigurationData($configurationData);
        $this->validateConfiguration($configurationObject);
    }

    /**
     * @test
     */
    public function createFromTypoScriptForPathTest()
    {
        $configurationObject = $this->fixture->createFromTypoScriptForResourceType(
            new ResourceType('ResourceType')
        );
        $this->validateConfiguration($configurationObject);
    }

    /**
     * @test
     */
    public function createFromJsonTest()
    {
        $configurationObject = $this->fixture->createFromJsonForResourceType(
            $this->getTestConfigurationJSONString(),
            new ResourceType('ResourceType')
        );
        $this->validateConfiguration($configurationObject);
    }


    /**
     * Runs the test on the given configuration
     *
     * @param ConfigurationInterface $configuration
     */
    public function validateConfiguration($configuration)
    {
        $this->assertInstanceOf(ConfigurationInterface::class, $configuration);

        $this->assertTrue($configuration->hasProperty('property1'), "Should have property 'property1'");

        $this->assertTrue($configuration->hasSourceKey('property_three'), "Should have source key 'property_three'");

        $this->assertEquals('property3', $configuration->getPropertyForSourceKey('property_three'));
        $this->assertEquals('property6', $configuration->getPropertyForSourceKey('property_six'));
        $this->assertEquals('property_seven', $configuration->getPropertyForSourceKey('property_seven'));
        $this->assertEquals('property_eight', $configuration->getPropertyForSourceKey('property_eight'));
        $this->assertNull($configuration->getPropertyForSourceKey('propertyNotExists'));

        $this->assertEquals('int', $configuration->getTypeForProperty('property3'));
        $this->assertEquals('boolean', $configuration->getTypeForProperty('property6'));
        $this->assertEquals('boolean', $configuration->getTypeForProperty('property_eight'));
        $this->assertNull($configuration->getTypeForProperty('propertyNotExists'));

        $this->assertEquals('my_resource_table', $configuration->getSourceIdentifier());
        $this->assertEquals('property1', $configuration->getIdentifier());
    }
}
