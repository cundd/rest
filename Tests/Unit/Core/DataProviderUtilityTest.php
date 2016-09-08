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

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\DataProvider\Utility;

require_once __DIR__ . '/../../Bootstrap.php';

/**
 * Test case for class new \Cundd\Rest\App
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author Daniel Corn <info@cundd.net>
 */
class DataProviderUtilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getClassNamePartsForPathTest()
    {
        $this->assertEquals(array('', 'MyExt', 'MyModel'), Utility::getClassNamePartsForPath('my_ext-my_model'));
        $this->assertEquals(array('Vendor', 'MyExt', 'MyModel'), Utility::getClassNamePartsForPath('vendor-my_ext-my_model'));
        $this->assertEquals(array('Vendor', 'MyExt', 'Group\\Model'), Utility::getClassNamePartsForPath('vendor-my_ext-group-model'));
        $this->assertEquals(array('Vendor', 'MyExt', 'Group\\MyModel'), Utility::getClassNamePartsForPath('vendor-my_ext-group-my_model'));
        $this->assertEquals(array('Vendor', 'MyExt', 'MyGroup\\MyModel'), Utility::getClassNamePartsForPath('vendor-my_ext-my_group-my_model'));
        $this->assertEquals(array('MyVendor', 'Ext', 'Group\\Model'), Utility::getClassNamePartsForPath('my_vendor-ext-group-model'));
    }

    /**
     * @test
     */
    public function getPathForClassNameTest()
    {
        $this->assertEquals('my_ext-my_model', Utility::getPathForClassName('Tx_MyExt_Domain_Model_MyModel'));
        $this->assertEquals('my_ext-my_model', Utility::getPathForClassName('MyExt\\Domain\\Model\\MyModel'));
        $this->assertEquals('vendor-my_ext-my_model', Utility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MyModel'));

        $this->assertEquals('my_ext-my_second_model', Utility::getPathForClassName('Tx_MyExt_Domain_Model_MySecondModel'));
        $this->assertEquals('my_ext-my_second_model', Utility::getPathForClassName('MyExt\\Domain\\Model\\MySecondModel'));
        $this->assertEquals('vendor-my_ext-my_second_model', Utility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MySecondModel'));

        $this->assertEquals('my_ext-my_model', Utility::getPathForClassName('MyExt\\MyModel'));
        $this->assertEquals('vendor-my_ext-my_model', Utility::getPathForClassName('Vendor\\MyExt\\MyModel'));
        $this->assertEquals('vendor-my_ext-group-model', Utility::getPathForClassName('Vendor\\MyExt\\Group\\Model'));
        $this->assertEquals('vendor-my_ext-group-my_model', Utility::getPathForClassName('Vendor\\MyExt\\Group\\MyModel'));
        $this->assertEquals('vendor-my_ext-my_group-my_model', Utility::getPathForClassName('Vendor\\MyExt\\MyGroup\\MyModel'));
        $this->assertEquals('my_vendor-ext-group-model', Utility::getPathForClassName('MyVendor\\Ext\\Group\\Model'));
    }

    /**
     * @test
     */
    public function singularizeTest()
    {
        $this->assertEquals('tree', Utility::singularize('trees'));
        $this->assertEquals('friend', Utility::singularize('friends'));
        $this->assertEquals('hobby', Utility::singularize('hobbies'));

        $this->assertEquals('Tree', Utility::singularize('Trees'));
        $this->assertEquals('Friend', Utility::singularize('Friends'));
        $this->assertEquals('Hobby', Utility::singularize('Hobbies'));
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
            Utility::registerSingularForPlural($singular, $plural);
        }

        $this->assertEquals('tree', Utility::singularize('trees'));
        $this->assertEquals('friend', Utility::singularize('friends'));
        $this->assertEquals('hobby', Utility::singularize('hobbies'));
        $this->assertEquals('Tree', Utility::singularize('Trees'));
        $this->assertEquals('Friend', Utility::singularize('Friends'));
        $this->assertEquals('Hobby', Utility::singularize('Hobbies'));
        $this->assertEquals('Singular', Utility::singularize('Plural'));

        foreach ($singularToPlural as $singular => $plural) {
            $this->assertEquals($singular, Utility::singularize($plural));
        }
    }
}
