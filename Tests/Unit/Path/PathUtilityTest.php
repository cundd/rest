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

namespace Cundd\Rest\Tests\Unit\Path;

use Cundd\Rest\Path\PathInfo;
use Cundd\Rest\Path\PathUtility;

require_once __DIR__ . '/../../Bootstrap.php';

/**
 * Test case for \Cundd\Rest\Path\PathUtility
 *
 * @author Daniel Corn <info@cundd.net>
 */
class PathUtilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getClassNamePartsForPathTest()
    {
        $this->assertPathInfoEquals(array('', 'MyExt', 'MyModel'), PathUtility::getClassNamePartsForPath('my_ext-my_model'));
        $this->assertPathInfoEquals(array('Vendor', 'MyExt', 'MyModel'), PathUtility::getClassNamePartsForPath('vendor-my_ext-my_model'));
        $this->assertPathInfoEquals(array('Vendor', 'MyExt', 'Group\\Model'), PathUtility::getClassNamePartsForPath('vendor-my_ext-group-model'));
        $this->assertPathInfoEquals(array('Vendor', 'MyExt', 'Group\\MyModel'), PathUtility::getClassNamePartsForPath('vendor-my_ext-group-my_model'));
        $this->assertPathInfoEquals(array('Vendor', 'MyExt', 'MyGroup\\MyModel'), PathUtility::getClassNamePartsForPath('vendor-my_ext-my_group-my_model'));
        $this->assertPathInfoEquals(array('MyVendor', 'Ext', 'Group\\Model'), PathUtility::getClassNamePartsForPath('my_vendor-ext-group-model'));
    }

    /**
     * @param array $expected
     * @param PathInfo $actual
     */
    private function assertPathInfoEquals(array $expected, $actual)
    {
        $this->assertInstanceOf('Cundd\\Rest\\Path\\PathInfo', $actual);

        list($vendor, $extension, $model) = $expected;
        $this->assertSame($vendor, $actual->getVendor());
        $this->assertSame($extension, $actual->getExtension());
        $this->assertSame($model, $actual->getModel());
    }

    /**
     * @test
     */
    public function getPathForClassNameTest()
    {
        $this->assertEquals('my_ext-my_model', PathUtility::getPathForClassName('Tx_MyExt_Domain_Model_MyModel'));
        $this->assertEquals('my_ext-my_model', PathUtility::getPathForClassName('MyExt\\Domain\\Model\\MyModel'));
        $this->assertEquals('vendor-my_ext-my_model', PathUtility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MyModel'));

        $this->assertEquals('my_ext-my_second_model', PathUtility::getPathForClassName('Tx_MyExt_Domain_Model_MySecondModel'));
        $this->assertEquals('my_ext-my_second_model', PathUtility::getPathForClassName('MyExt\\Domain\\Model\\MySecondModel'));
        $this->assertEquals('vendor-my_ext-my_second_model', PathUtility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MySecondModel'));

        $this->assertEquals('my_ext-my_model', PathUtility::getPathForClassName('MyExt\\MyModel'));
        $this->assertEquals('vendor-my_ext-my_model', PathUtility::getPathForClassName('Vendor\\MyExt\\MyModel'));
        $this->assertEquals('vendor-my_ext-group-model', PathUtility::getPathForClassName('Vendor\\MyExt\\Group\\Model'));
        $this->assertEquals('vendor-my_ext-group-my_model', PathUtility::getPathForClassName('Vendor\\MyExt\\Group\\MyModel'));
        $this->assertEquals('vendor-my_ext-my_group-my_model', PathUtility::getPathForClassName('Vendor\\MyExt\\MyGroup\\MyModel'));
        $this->assertEquals('my_vendor-ext-group-model', PathUtility::getPathForClassName('MyVendor\\Ext\\Group\\Model'));
    }

    /**
     * @test
     */
    public function singularizeTest()
    {
        $this->assertEquals('tree', PathUtility::singularize('trees'));
        $this->assertEquals('friend', PathUtility::singularize('friends'));
        $this->assertEquals('hobby', PathUtility::singularize('hobbies'));

        $this->assertEquals('Tree', PathUtility::singularize('Trees'));
        $this->assertEquals('Friend', PathUtility::singularize('Friends'));
        $this->assertEquals('Hobby', PathUtility::singularize('Hobbies'));
    }

    /**
     * @test
     */
    public function registerSingularForPluralTest()
    {
        $singularToPlural = array(
            'news' => 'news',
            'equipment' => 'equipment',
            'species' => 'species',
            'series' => 'series',
            'News' => 'News',
            'Equipment' => 'Equipment',
            'Species' => 'Species',
            'Series' => 'Series',
            'Singular' => 'Plural',
        );
        foreach ($singularToPlural as $singular => $plural) {
            PathUtility::registerSingularForPlural($singular, $plural);
        }

        $this->assertEquals('tree', PathUtility::singularize('trees'));
        $this->assertEquals('friend', PathUtility::singularize('friends'));
        $this->assertEquals('hobby', PathUtility::singularize('hobbies'));
        $this->assertEquals('Tree', PathUtility::singularize('Trees'));
        $this->assertEquals('Friend', PathUtility::singularize('Friends'));
        $this->assertEquals('Hobby', PathUtility::singularize('Hobbies'));
        $this->assertEquals('Singular', PathUtility::singularize('Plural'));

        foreach ($singularToPlural as $singular => $plural) {
            $this->assertEquals($singular, PathUtility::singularize($plural));
        }
    }
}
