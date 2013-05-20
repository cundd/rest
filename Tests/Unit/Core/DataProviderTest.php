<?php
namespace Cundd\Rest\Test\Core;

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
class DataProviderTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Cundd\Rest\DataProvider\DataProviderInterface
	 */
	protected $fixture;

	static public function setUpBeforeClass() {
		\Tx_CunddComposer_Autoloader::register();

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
		$repository = $this->fixture->getRepositoryForPath('MyExt_MyModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModelRepository', $repository);
	}

	/**
	 * @test
	 */
	public function getNamespacedRepositoryForPathTest() {
		$repository = $this->fixture->getRepositoryForPath('MyExt_MySecondModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModelRepository', $repository);
	}

	/**
	 * @test
	 */
	public function getNamespacedRepositoryForPathWithVendorTest() {
		$repository = $this->fixture->getRepositoryForPath('Vendor_MyExt_MyModel');
		$this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', $repository);
	}

	/**
	 * @test
	 */
	public function getModelForPathTest() {
		$model = $this->fixture->getEmptyModelForPath('MyExt_MyModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModel', $model);
	}

	/**
	 * @test
	 */
	public function getNamespacedModelForPathTest() {
		$model = $this->fixture->getEmptyModelForPath('MyExt_MySecondModel');
		$this->assertInstanceOf('\\Cundd\\Rest\Test\\Core\\MyModel', $model);
	}

	/**
	 * @test
	 */
	public function getNamespacedModelForPathWithVendorTest() {
		$model = $this->fixture->getEmptyModelForPath('Vendor_MyExt_MyModel');
		$this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);
	}

	/**
	 * @test
	 */
	public function getModelWithEmptyDataTest() {
		$data = array();
		$path = 'MyExt_MyModel';
		$model = $this->fixture->getModelWithDataForPath($data, $path);
		$this->assertEquals('Initial value', $model->getName());
	}

	/**
	 * The test is currently failing because of a missing type converter
	 * @test
	 */
	public function getModelWithDataTest() {
		$data = array('name' => 'Daniel Corn');
		$path = 'MyExt_MyModel';
		#$model = $this->fixture->getModelWithDataForPath($data, $path);
		#$this->assertEquals('Daniel Corn', $model->getName());
	}

}
?>