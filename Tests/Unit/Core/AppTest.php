<?php
namespace Cundd\Rest\Test\Core;



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

	public function setUp() {
		\Tx_CunddComposer_Autoloader::register();
		$this->fixture = new \Cundd\Rest\App;
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getUriTest() {
		$_GET['u'] = 'MyExt_MyModel/1';
		$uri = $this->fixture->getUri();
		$this->assertEquals('MyExt_MyModel/1', $uri);
	}

	/**
	 * @test
	 */
	public function getPathTest() {
		$_GET['u'] = 'MyExt_MyModel/1';
		$path = $this->fixture->getPath();
		$this->assertEquals('MyExt_MyModel', $path);
	}
}
?>