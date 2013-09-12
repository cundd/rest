<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 21:13
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Test\Core;

\Tx_CunddComposer_Autoloader::register();
class ObjectManagerTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \Cundd\Rest\ObjectManager
	 */
	protected $fixture;
	
	public function setUp() {
		$this->fixture = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Cundd\\Rest\\ObjectManager');
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getConfigurationProviderTest() {
		$object = $this->fixture->getConfigurationProvider();
		$this->assertInstanceOf('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider', $object);
	}

	/**
	 * @test
	 */
	public function getDataProviderTest() {
		$object = $this->fixture->getDataProvider();
		$this->assertInstanceOf('Cundd\\Rest\\DataProvider\\DataProviderInterface', $object);
		$this->assertInstanceOf('Cundd\\Rest\\DataProvider\\DataProvider', $object);
	}

	/**
	 * @test
	 */
	public function getAuthenticationProviderTest() {
		$object = $this->fixture->getAuthenticationProvider();
		$this->assertInstanceOf('Cundd\\Rest\\Authentication\\AuthenticationProviderInterface', $object);
	}

}
