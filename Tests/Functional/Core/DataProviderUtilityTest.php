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

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Tests\Functional\AbstractCase;

require_once __DIR__ . '/../AbstractCase.php';

/**
 * Test case for class new \Cundd\Rest\App
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class DataProviderUtilityTest extends AbstractCase {
    /**
     * @test
     */
    public function getClassNamePartsForPathTest() {
        $this->assertEquals(array('', 'MyExt', 'MyModel'), Utility::getClassNamePartsForPath('my_ext-my_model'));
    }

    /**
     * @test
     */
    public function getPathForClassNameTest() {
        $this->assertEquals('my_ext-my_model', Utility::getPathForClassName('Tx_MyExt_Domain_Model_MyModel'));
        $this->assertEquals('my_ext-my_model', Utility::getPathForClassName('MyExt\\Domain\\Model\\MyModel'));
        $this->assertEquals('my_ext-my_model', Utility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MyModel'));

        $this->assertEquals('my_ext-my_second_model', Utility::getPathForClassName('Tx_MyExt_Domain_Model_MySecondModel'));
        $this->assertEquals('my_ext-my_second_model', Utility::getPathForClassName('MyExt\\Domain\\Model\\MySecondModel'));
        $this->assertEquals('my_ext-my_second_model', Utility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MySecondModel'));
    }

    /**
     * @test
     */
    public function singularizeTest() {
        $this->assertEquals('tree', Utility::singularize('trees'));
        $this->assertEquals('friend', Utility::singularize('friends'));
        $this->assertEquals('hobby', Utility::singularize('hobbies'));
        $this->assertEquals('news', Utility::singularize('news'));
        $this->assertEquals('equipment', Utility::singularize('equipment'));
        $this->assertEquals('species', Utility::singularize('species'));
        $this->assertEquals('series', Utility::singularize('series'));

        $this->assertEquals('tree', Utility::singularize('Trees'));
        $this->assertEquals('friend', Utility::singularize('Friends'));
        $this->assertEquals('hobby', Utility::singularize('Hobbies'));
        $this->assertEquals('news', Utility::singularize('News'));
        $this->assertEquals('equipment', Utility::singularize('Equipment'));
        $this->assertEquals('species', Utility::singularize('Species'));
        $this->assertEquals('series', Utility::singularize('Series'));
    }
}
