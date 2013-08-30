<?php
namespace Cundd\Rest\Test\Core;

class DummyObject {}

/**
 * Test case for class new \Cundd\Rest\App
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author Daniel Corn <cod@iresults.li>
 */
class AppTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Cundd\Rest\App
	 */
	protected $fixture;

	public static function setUpBeforeClass() {
		class_alias('\\Cundd\\Rest\\Test\\Core\\DummyObject', 'Tx_MyExt_Rest_DataProvider');
		class_alias('\\Cundd\\Rest\\Test\\Core\\DummyObject', 'Vendor\\MySecondExt\\Rest\\DataProvider');
	}


	public function setUp() {
		\Tx_CunddComposer_Autoloader::register();
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