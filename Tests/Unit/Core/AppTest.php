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
class DummyObject {}

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
class AppTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Cundd\Rest\App
	 */
	protected $fixture;

	public static function setUpBeforeClass() {
		class_alias('Cundd\\Rest\\DataProvider\\DataProvider', 'Tx_MyExt_Rest_DataProvider');
		class_alias('Cundd\\Rest\\DataProvider\\DataProvider', 'Vendor\\MySecondExt\\Rest\\DataProvider');
	}


	public function setUp() {
		$this->fixture = new \Cundd\Rest\App;
	}

	public function tearDown() {
		unset($this->fixture);
		unset($_GET['u']);
	}

	/**
	 * @test
	 */
	public function getUriTest() {
		$_GET['u'] = 'MyExt-MyModel/1';
		$request = $this->fixture->getRequest();
		$this->assertEquals('MyExt-MyModel/1', $request->url());
		$this->assertEquals('html', $request->format());
	}

	/**
	 * @test
	 */
	public function getUriWithFormatTest() {
		$_GET['u'] = 'MyExt-MyModel/1.json';
		$request = $this->fixture->getRequest();
		$this->assertEquals('MyExt-MyModel/1', $request->url());
		$this->assertEquals('json', $request->format());
	}

	/**
	 * @test
	 */
	public function getPathTest() {
		$_GET['u'] = 'MyExt-MyModel/1';
		$path = $this->fixture->getPath();
		$this->assertEquals('MyExt-MyModel', $path);
	}

	/**
	 * @test
	 */
	public function getPathWithFormatTest() {
		$_GET['u'] = 'MyExt-MyModel/1.json';
		$path = $this->fixture->getPath();
		$this->assertEquals('MyExt-MyModel', $path);
	}

	/**
	 * @test
	 */
	public function getUnderscoredPathWithFormatAndIdTest() {
		$_GET['u'] = 'my_ext-my_model/1.json';
		$path = $this->fixture->getPath();
		$this->assertEquals('my_ext-my_model', $path);
	}

	/**
	 * @test
	 */
	public function getUnderscoredPathWithFormatTest2() {
		$_GET['u'] = 'my_ext-my_model.json';
		$path = $this->fixture->getPath();
		$this->assertEquals('my_ext-my_model', $path);
	}

	/**
	 * @test
	 */
	public function getDataProviderForPathTest() {
		$_GET['u'] = 'my_ext-my_model/1';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getDataProviderForPathWithFormatTest() {
		$_GET['u'] = 'my_ext-my_model/1.json';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getDataProviderForPathUpperCamelCaseTest() {
		$_GET['u'] = 'MyExt-MyModel/1';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getDataProviderForPathUpperCamelCaseWithFormatTest() {
		$_GET['u'] = 'MyExt-MyModel/1.json';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('Tx_MyExt_Rest_DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getNamespacedDataProviderForPathTest() {
		$_GET['u'] = 'vendor-my_second_ext-my_model/1';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getNamespacedDataProviderForPathUpperCamelCaseTest() {
		$_GET['u'] = 'Vendor-MySecondExt-MyModel/1';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('\\Vendor\\MySecondExt\\Rest\\DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getDefaultDataProviderForPathTest() {
		$_GET['u'] = 'Vendor-NotExistingExt-MyModel/1';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('\\Cundd\\Rest\\DataProvider\\DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getDefaultDataProviderForPathWithFormatTest() {
		$_GET['u'] = 'Vendor-NotExistingExt-MyModel/1.json';
		$dataProvider = $this->fixture->getDataProvider();
		$this->assertInstanceOf('\\Cundd\\Rest\\DataProvider\\DataProvider', $dataProvider);
	}

	/**
	 * @test
	 */
	public function getFormatWithoutFormatTest() {
		$_GET['u'] = 'MyExt-MyModel/1';
		$request = $this->fixture->getRequest();
		$this->assertEquals('html', $request->format());
	}

	/**
	 * @test
	 */
	public function getFormatWithFormatTest() {
		$_GET['u'] = 'MyExt-MyModel/1.json';
		$request = $this->fixture->getRequest();
		$this->assertEquals('json', $request->format());
	}

	/**
	 * @test
	 */
	public function getFormatWithNotExistingFormatTest() {
		$_GET['u'] = 'MyExt-MyModel/1.blur';
		$request = $this->fixture->getRequest();
		$this->assertEquals('html', $request->format());
	}


}
?>
