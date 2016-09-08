<?php
/*
 *  Copyright notice
 *
 *  (c) 2016 Daniel Corn <info@cundd.net>, cundd
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

namespace Cundd\Rest\Tests\Functional\VirtualObject;

require_once __DIR__ . '/AbstractVirtualObjectCase.php';

/**
 * Class ConfigurationTest
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class ConfigurationTest extends AbstractVirtualObjectCase
{
    /**
     * @var \Cundd\Rest\VirtualObject\Configuration
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        $testConfiguration = $this->getTestConfigurationData();
        $this->fixture = new \Cundd\Rest\VirtualObject\Configuration(
            \Cundd\Rest\VirtualObject\ConfigurationFactory::preparePropertyMapping($testConfiguration['ResourceName']['mapping'])
        );
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAllPropertiesTest()
    {
        $this->assertEquals(array(
            'property1',
            'property2',
            'property3',
            'property4',
            'property5',
            'property6',
            'property_seven',
            'property_eight',
        ), $this->fixture->getAllProperties());
    }

    /**
     * @test
     */
    public function getAllSourceKeysTest()
    {
        $this->assertEquals(array(
            'property_one',
            'property_two',
            'property_three',
            'property_four',
            'property_five',
            'property_six',
            'property_seven',
            'property_eight',
        ), $this->fixture->getAllSourceKeys());
    }

    /**
     * @test
     */
    public function hasPropertyTest()
    {
        $this->assertTrue($this->fixture->hasProperty('property1'));
        $this->assertTrue($this->fixture->hasProperty('property2'));
        $this->assertTrue($this->fixture->hasProperty('property3'));
        $this->assertTrue($this->fixture->hasProperty('property4'));
        $this->assertTrue($this->fixture->hasProperty('property5'));
        $this->assertTrue($this->fixture->hasProperty('property6'));
        $this->assertTrue($this->fixture->hasProperty('property_seven'));
        $this->assertTrue($this->fixture->hasProperty('property_eight'));
        $this->assertFalse($this->fixture->hasProperty('propertyNotExists'));
    }

    /**
     * @test
     */
    public function hasSourceKeyTest()
    {
        $this->assertTrue($this->fixture->hasSourceKey('property_one'));
        $this->assertTrue($this->fixture->hasSourceKey('property_two'));
        $this->assertTrue($this->fixture->hasSourceKey('property_three'));
        $this->assertTrue($this->fixture->hasSourceKey('property_four'));
        $this->assertTrue($this->fixture->hasSourceKey('property_five'));
        $this->assertTrue($this->fixture->hasSourceKey('property_six'));
        $this->assertTrue($this->fixture->hasSourceKey('property_seven'));
        $this->assertTrue($this->fixture->hasSourceKey('property_eight'));
        $this->assertFalse($this->fixture->hasSourceKey('property_not_exists'));
    }

    /**
     * @test
     */
    public function getConfigurationForPropertyTest()
    {
        $testPropertyConfiguration = array(
            'type' => 'string',
            'column' => 'property_one',
        );
        $propertyConfiguration = $this->fixture->getConfigurationForProperty('property1');
        $this->assertEquals($testPropertyConfiguration, $propertyConfiguration);
        $this->assertEmpty($this->fixture->getConfigurationForProperty('propertyNotExists'));
    }

    /**
     * @test
     */
    public function getSourceKeyForPropertyTest()
    {
        $this->assertEquals('property_three', $this->fixture->getSourceKeyForProperty('property3'));
        $this->assertEquals('property_six', $this->fixture->getSourceKeyForProperty('property6'));
        $this->assertNull($this->fixture->getSourceKeyForProperty('propertyNotExists'));
    }

    /**
     * @test
     */
    public function getPropertyForSourceKeyTest()
    {
        $this->assertEquals('property3', $this->fixture->getPropertyForSourceKey('property_three'));
        $this->assertEquals('property6', $this->fixture->getPropertyForSourceKey('property_six'));
        $this->assertNull($this->fixture->getPropertyForSourceKey('propertyNotExists'));
    }

    /**
     * @test
     */
    public function getTypeForPropertyTest()
    {
        $this->assertEquals('int', $this->fixture->getTypeForProperty('property3'));
        $this->assertEquals('boolean', $this->fixture->getTypeForProperty('property6'));
        $this->assertNull($this->fixture->getTypeForProperty('propertyNotExists'));
    }

    /**
     * @test
     */
    public function getSourceIdentifierTest()
    {
        $this->assertEquals('my_resource_table', $this->fixture->getSourceIdentifier());
    }

    /**
     * @test
     */
    public function getIdentifierTest()
    {
        $this->assertEquals('property1', $this->fixture->getIdentifier());
    }
}
