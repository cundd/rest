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

\Tx_CunddComposer_Autoloader::register();

class MyModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {
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
class MyModelRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {}

class MyNestedModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {
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

class MyNestedJsonSerializeModel extends MyNestedModel {
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
class DataProviderTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Cundd\Rest\DataProvider\DataProviderInterface
	 */
	protected $fixture;

	static public function setUpBeforeClass() {
		class_alias('\\Cundd\\Rest\Test\\Core\\MyModel', 'Tx_MyExt_Domain_Model_MyModel');
		class_alias('\\Cundd\\Rest\Test\\Core\\MyModelRepository', 'Tx_MyExt_Domain_Repository_MyModelRepository');

		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModel', 'MyExt\\Domain\\Model\\MySecondModel');
		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModelRepository', 'MyExt\\Domain\\Repository\\MySecondModelRepository');

		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModel', 'Vendor\\MyExt\\Domain\\Model\\MyModel');
		class_alias('\\Cundd\\Rest\\Test\\Core\\MyModelRepository', 'Vendor\\MyExt\\Domain\\Repository\\MyModelRepository');
	}

	public function setUp() {
		$this->fixture = $this->objectManager->get('Cundd\\Rest\\DataProvider\\DataProvider');
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getRepositoryForPathTest() {
		$repository = $this->fixture->getRepositoryForPath('MyExt-MyModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModelRepository', $repository);

		$repository = $this->fixture->getRepositoryForPath('my_ext-my_model');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModelRepository', $repository);
	}

	/**
	 * @test
	 */
	public function getNamespacedRepositoryForPathTest() {
		$repository = $this->fixture->getRepositoryForPath('MyExt-MySecondModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModelRepository', $repository);

		$repository = $this->fixture->getRepositoryForPath('my_ext-my_second_model');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModelRepository', $repository);
	}

	/**
	 * @test
	 */
	public function getNamespacedRepositoryForPathWithVendorTest() {
		$repository = $this->fixture->getRepositoryForPath('Vendor-MyExt-MyModel');
		$this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', $repository);

		$repository = $this->fixture->getRepositoryForPath('vendor-my_ext-my_model');
		$this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', $repository);
	}

	/**
	 * @test
	 */
	public function getModelForPathTest() {
		$model = $this->fixture->getModelWithDataForPath(array(), 'MyExt-MyModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModel', $model);

		$model = $this->fixture->getModelWithDataForPath(array(), 'my_ext-my_model');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModel', $model);
	}

	/**
	 * @test
	 */
	public function getNamespacedModelForPathTest() {
		$model = $this->fixture->getModelWithDataForPath(array(), 'MyExt-MySecondModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModel', $model);

		$model = $this->fixture->getModelWithDataForPath(array(), 'my_ext-my_second_model');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModel', $model);
	}

	/**
	 * @test
	 */
	public function getNamespacedModelForPathWithVendorTest() {
		$model = $this->fixture->getModelWithDataForPath(array(), 'Vendor-MyExt-MyModel');
		$this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);

		$model = $this->fixture->getModelWithDataForPath(array(), 'vendor-my_ext-my_model');
		$this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);
	}

	/**
	 * @test
	 */
	public function getModelWithEmptyDataTest() {
		$data = array();
		$path = 'MyExt-MyModel';

		/** @var \Cundd\Rest\Test\Core\MyModel $model */
		$model = $this->fixture->getModelWithDataForPath($data, $path);
		$this->assertEquals('Initial value', $model->getName());
	}

	/**
	 * The test is currently failing because of a missing type converter
	 * @test
	 */
	public function getModelWithDataTest() {
		$data = array('name' => 'Daniel Corn');
		$path = 'MyExt-MyModel';
//		$model = $this->fixture->getModelWithDataForPath($data, $path);
//		$this->assertEquals('Daniel Corn', $model->getName());
	}

	/**
	 * The test is currently failing because of a missing type converter
	 * @test
	 */
	public function getNewModelWithDataTest() {
		$data = array('name' => 'Daniel Corn', '__identity' => 9);
		$path = 'MyExt-MyModel';
//		$model = $this->fixture->getModelWithDataForPath($data, $path);
//		$this->assertEquals('Daniel Corn', $model->getName());
//		$this->assertEquals(9, $model->getUid());
	}

	/**
	 * @test
	 */
	public function getModelDataTest() {
		$model = new MyModel();
		$properties = $this->fixture->getModelData($model);
		$this->assertEquals(
			array(
				'name' 		=> 'Initial value',
				'uid' 		=> NULL,
				'pid' 		=> NULL,
				'__class' 	=>'Cundd\\Rest\\Test\\Core\\MyModel'
			), $properties);
	}

	/**
	 * @test
	 */
	public function getNestedModelDataTest() {
		$testDate = new \DateTime();
		$model = new MyNestedModel();
		$model->setDate($testDate);

		$properties = $this->fixture->getModelData($model);
		$this->assertEquals(
			array(
				'base' 		=> 'Base',
				'date' 		=> $testDate,
				'uid' 		=> NULL,
				'pid' 		=> NULL,
				'child' 	=> array(
					'name' 		=> 'Initial value',
					'uid' 		=> NULL,
					'pid' 		=> NULL,
					'__class' 	=>'Cundd\\Rest\\Test\\Core\\MyModel'
				),
				'__class' 	=>'Cundd\\Rest\\Test\\Core\\MyNestedModel'
			), $properties);
	}

	/**
	 * @test
	 */
	public function getJsonSerializeNestedModelDataTest() {
		$model = new MyNestedJsonSerializeModel();
		$properties = $this->fixture->getModelData($model);
		$this->assertEquals(
			array(
				'base' 		=> 'Base',
				'child' 	=> array(
					'name' 		=> 'Initial value',
					'uid' 		=> NULL,
					'pid' 		=> NULL,
					'__class' 	=>'Cundd\\Rest\\Test\\Core\\MyModel'
				),
				'__class' 	=>'Cundd\\Rest\\Test\\Core\\MyNestedJsonSerializeModel'
			), $properties);
	}

}
?>
