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

namespace Cundd\Rest\Test\Core;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Test\AbstractCase;

require_once __DIR__ . '/../AbstractCase.php';

class MyModel2 extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {
	/**
	 * @var string
	 */
	protected $name = 'Initial value';

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
}
class MyModelRepository2 extends \TYPO3\CMS\Extbase\Persistence\Repository {}

class MyNestedModel2 extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {
	/**
	 * @var string
	 */
	protected $base = 'Base';

	/**
	 * @var \DateTime
	 */
	protected $date = NULL;

	/**
	 * @var \Cundd\Rest\Test\Core\MyModel
	 */
	protected $child = NULL;

	function __construct() {
		$this->child = new MyModel();
		$this->date = new \DateTime();
	}


	/**
	 * @param string $base
	 */
	public function setBase($base) {
		$this->base = $base;
	}

	/**
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 * @param \Cundd\Rest\Test\Core\MyModel $child
	 */
	public function setChild($child) {
		$this->child = $child;
	}

	/**
	 * @return \Cundd\Rest\Test\Core\MyModel
	 */
	public function getChild() {
		return $this->child;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setDate($date) {
		$this->date = $date;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}
}

class MyNestedJsonSerializeModel2 extends MyNestedModel {
	public function jsonSerialize() {
		return array(
			'base' 	=> $this->base,
			'child'	=> $this->child
		);
	}
}

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
	static public function setUpBeforeClass() {
		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModel2', 'Tx_MyExt_Domain_Model_MyModel2');
		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModelRepository2', 'Tx_MyExt_Domain_Repository_MyModelRepository2');

		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModel2', 'MyExt\\Domain\\Model\\MySecondModel2');
		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModelRepository2', 'MyExt\\Domain\\Repository\\MySecondModelRepository2');

		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModel2', 'Vendor\\MyExt\\Domain\\Model\\MyModel2');
		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModelRepository2', 'Vendor\\MyExt\\Domain\\Repository\\MyModelRepository2');
	}

	/**
	 * @test
	 */
	public function getClassNamePartsForPathTest() {
		$this->assertEquals(array('', 'MyExt', 'MyModel2'), Utility::getClassNamePartsForPath('my_ext-my_model2'));
	}

	/**
	 * @test
	 */
	public function getPathForClassNameTest() {
		$this->assertEquals('my_ext-my_model2', Utility::getPathForClassName('Tx_MyExt_Domain_Model_MyModel2'));
		$this->assertEquals('my_ext-my_model2', Utility::getPathForClassName('MyExt\\Domain\\Model\\MyModel2'));
		$this->assertEquals('my_ext-my_model2', Utility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MyModel2'));

		$this->assertEquals('my_ext-my_second_model2', Utility::getPathForClassName('Tx_MyExt_Domain_Model_MySecondModel2'));
		$this->assertEquals('my_ext-my_second_model2', Utility::getPathForClassName('MyExt\\Domain\\Model\\MySecondModel2'));
		$this->assertEquals('my_ext-my_second_model2', Utility::getPathForClassName('Vendor\\MyExt\\Domain\\Model\\MySecondModel2'));
	}

    /**
     * @test
     */
    public function singularizeTest()
    {
        $this->assertEquals('tree', Utility::singularize('trees'));
        $this->assertEquals('friend', Utility::singularize('friends'));
        $this->assertEquals('hobby', Utility::singularize('hobbies'));
        $this->assertEquals('news', Utility::singularize('news'));
        $this->assertEquals('equipment', Utility::singularize('equipment'));
        $this->assertEquals('species', Utility::singularize('species'));
        $this->assertEquals('series', Utility::singularize('series'));
    }
}
?>
