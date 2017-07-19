<?php


namespace Cundd\Rest\Tests\Functional\VirtualObject;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\VirtualObject\ConfigurationFactory;
use Cundd\Rest\VirtualObject\ConfigurationInterface;

require_once __DIR__ . '/AbstractVirtualObjectCase.php';

/**
 * Class ConfigurationTest
 */
class ConfigurationFactoryTest extends AbstractVirtualObjectCase
{
    /**
     * @var \Cundd\Rest\VirtualObject\ConfigurationFactory
     */
    protected $fixture;

    protected $typoScriptDummyArray = [
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
    ];


    public function setUp()
    {
        parent::setUp();
        $this->fixture = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\ConfigurationFactory');

        /** @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider|\PHPUnit_Framework_MockObject_MockObject $typeScriptConfigurationStub */
        $typeScriptConfigurationStub = $this->getMockObjectGenerator()->getMock(
            'Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider'
        );
        $typeScriptConfigurationStub->expects($this->any())
            ->method('getSetting')
            ->will($this->returnValue($this->typoScriptDummyArray));

        $this->fixture = new ConfigurationFactory($typeScriptConfigurationStub);
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
        $this->assertInstanceOf('Cundd\\Rest\\VirtualObject\\ConfigurationInterface', $this->fixture->create());
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
        $configurationData = $this->typoScriptDummyArray['resource_type']['mapping.'];
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
        $this->assertInstanceOf('Cundd\\Rest\\VirtualObject\\ConfigurationInterface', $configuration);

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
