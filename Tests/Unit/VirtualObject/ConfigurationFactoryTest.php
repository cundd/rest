<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 11:09
 */

namespace Cundd\Rest\Test\VirtualObject;

use Cundd\Rest\VirtualObject\ConfigurationInterface;

require_once __DIR__ . '/AbstractVirtualObjectCase.php';

/**
 * Class ConfigurationTest
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class ConfigurationFactoryTest extends AbstractVirtualObjectCase {
	/**
	 * @var \Cundd\Rest\VirtualObject\ConfigurationFactory
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\ConfigurationFactory');


		$typoScriptDummyArray = array(
			'mapping.' => array(
				'identifier'  => 'property1',
				'tableName'   => 'my_resource_table',
				'properties.' => array(
					'property1.' => array(
						'type'   => 'string',
						'column' => 'property_one',
					),
					'property2.' => array(
						'type'   => 'float',
						'column' => 'property_two',
					),
					'property3.' => array(
						'type'   => 'int',
						'column' => 'property_three',
					),
					'property4.' => array(
						'type'   => 'integer',
						'column' => 'property_four',
					),
					'property5.' => array(
						'type'   => 'bool',
						'column' => 'property_five',
					),
					'property6.' => array(
						'type'   => 'boolean',
						'column' => 'property_six',
					)
				)
			)
		);


		$typeScriptConfigurationStub = $this->getMock('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider');
		$typeScriptConfigurationStub->expects($this->any())
			->method('getSetting')
			->will($this->returnValue($typoScriptDummyArray));

		$this->fixture->injectConfigurationProvider($typeScriptConfigurationStub);
		parent::setUp();
	}

	public function tearDown() {
		unset($this->fixture);
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function createTest() {
		$this->assertInstanceOf('Cundd\\Rest\\VirtualObject\\ConfigurationInterface', $this->fixture->create());
	}

	/**
	 * @test
	 */
	public function createFromArrayTest() {
		$configurationObject = $this->fixture->createFromArrayForPath($this->getTestConfigurationData(), 'ResourceName');
		$this->validateConfiguration($configurationObject);
	}

	/**
	 * @test
	 */
	public function createFromTypoScriptForPathTest() {
		$configurationObject = $this->fixture->createFromTypoScriptForPath('ResourceName');
		$this->validateConfiguration($configurationObject);
	}

	/**
	 * @test
	 */
	public function createFromJsonTest() {
		$configurationObject = $this->fixture->createFromJsonForPath($this->getTestConfigurationJSONString(), 'ResourceName');
		$this->validateConfiguration($configurationObject);
	}


	/**
	 * Runs the test on the given configuration
	 *
	 * @param ConfigurationInterface $configuration
	 */
	public function validateConfiguration($configuration) {
		$this->assertInstanceOf('Cundd\\Rest\\VirtualObject\\ConfigurationInterface', $configuration);

		$this->assertTrue($configuration->hasProperty('property1'));

		$this->assertTrue($configuration->hasSourceKey('property_three'));

		$this->assertEquals('property3', $configuration->getPropertyForSourceKey('property_three'));
		$this->assertEquals('property6', $configuration->getPropertyForSourceKey('property_six'));
		$this->assertNull($configuration->getPropertyForSourceKey('propertyNotExists'));

		$this->assertEquals('int', $configuration->getTypeForProperty('property3'));
		$this->assertEquals('boolean', $configuration->getTypeForProperty('property6'));
		$this->assertNull($configuration->getTypeForProperty('propertyNotExists'));

		$this->assertEquals('my_resource_table', $configuration->getSourceIdentifier());
		$this->assertEquals('property1', $configuration->getIdentifier());
	}
}
