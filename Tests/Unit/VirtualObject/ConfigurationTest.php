<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 11:09
 */

namespace Cundd\Rest\Test\VirtualObject;

require_once __DIR__ . '/AbstractVirtualObject.php';

/**
 * Class ConfigurationTest
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class ConfigurationTest extends AbstractVirtualObject {
	/**
	 * @var \Cundd\Rest\VirtualObject\Configuration
	 */
	protected $fixture;

	public function setUp() {
		$testConfiguration = $this->getTestConfigurationData();
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
		$this->assertEquals('property_three', $this->fixture->getSourceKeyForProperty('property3'));
		$this->assertEquals('property_six', $this->fixture->getSourceKeyForProperty('property6'));
		$this->assertNull($this->fixture->getSourceKeyForProperty('propertyNotExists'));
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
}
 