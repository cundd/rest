<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 11:09
 */

namespace Cundd\Rest\Test\VirtualObject;

\Tx_CunddComposer_Autoloader::register();

/**
 * Class ConfigurationTest
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \Cundd\Rest\VirtualObject\Configuration
	 */
	protected $fixture;

	/**
	 * @var array
	 */
	protected $testConfiguration = array();

	public function setUp() {
		$testConfiguration = $this->getTestConfiguration();
		$this->fixture = new \Cundd\Rest\VirtualObject\Configuration($testConfiguration['ResourceName']['mapping']);
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function hasPropertyTest() {
		$this->assertTrue($this->fixture->hasProperty('property1'));
		$this->assertTrue($this->fixture->hasProperty('property2'));
		$this->assertTrue($this->fixture->hasProperty('property3'));
		$this->assertTrue($this->fixture->hasProperty('property4'));
		$this->assertTrue($this->fixture->hasProperty('property5'));
		$this->assertTrue($this->fixture->hasProperty('property6'));
		$this->assertFalse($this->fixture->hasProperty('propertyNotExists'));
	}

	/**
	 * @test
	 */
	public function getConfigurationForPropertyTest() {
		$testPropertyConfiguration = array(
			'type' => 'string',
			'column'=> 'property_one',
		);
		$propertyConfiguration = $this->fixture->getConfigurationForProperty('property1');
		$this->assertEquals($testPropertyConfiguration, $propertyConfiguration);
		$this->assertEmpty($this->fixture->getConfigurationForProperty('propertyNotExists'));
	}

	/**
	 * @test
	 */
	public function getSourcePropertyNameForPropertyTest() {
		$this->assertEquals('property_three', $this->fixture->getSourcePropertyNameForProperty('property3'));
		$this->assertEquals('property_six', $this->fixture->getSourcePropertyNameForProperty('property6'));
		$this->assertNull($this->fixture->getSourcePropertyNameForProperty('propertyNotExists'));
	}

	/**
	 * @test
	 */
	public function getTypeForPropertyTest() {
		$this->assertEquals('int', $this->fixture->getTypeForProperty('property3'));
		$this->assertEquals('boolean', $this->fixture->getTypeForProperty('property6'));
		$this->assertNull($this->fixture->getTypeForProperty('propertyNotExists'));
	}

	/**
	 * @test
	 */
	public function getSourceIdentifierTest() {
		$this->assertEquals('my_resource_table', $this->fixture->getSourceIdentifier());
	}


	protected function getTestConfiguration() {
		if ($this->testConfiguration) {
			return $this->testConfiguration;
		}
		$testConfigurationJson = <<<CONFIGURATION
{
    "ResourceName": {
        "mapping": {
            "tableName": "my_resource_table",

            "properties": {
                "property1": {
                    "type": "string",
                    "column": "property_one"
                },
                "property2": {
                    "type": "float",
                    "column": "property_two"
                },
                "property3": {
                    "type": "int",
                    "column": "property_three"
                },
                "property4": {
                    "type": "integer",
                    "column": "property_four"
                },
                "property5": {
                    "type": "bool",
                    "column": "property_five"
                },
                "property6": {
                    "type": "boolean",
                    "column": "property_six"
                }
            }
        }
    }
}
CONFIGURATION;

		$this->testConfiguration = json_decode($testConfigurationJson, TRUE);
		return $this->testConfiguration;
	}
}
 