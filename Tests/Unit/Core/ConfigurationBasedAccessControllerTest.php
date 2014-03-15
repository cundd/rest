<?php
namespace Cundd\Rest\Test\Core;

\Tx_CunddComposer_Autoloader::register();
use Cundd\Rest\Request;

class ConfigurationBasedAccessControllerTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Cundd\Rest\Access\ConfigurationBasedAccessController
	 */
	protected $fixture;

	public function setUp() {
		$configurationProvider = $this->objectManager->get('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider');
		$this->fixture = $this->objectManager->get('Cundd\\Rest\\Access\\ConfigurationBasedAccessController');

		$settings = array(
			'paths' =>
			array(
				'1.' =>
				array(
					'path' => 'all',
					'read' => 'allow',
					'write' => 'deny',
				),
				'2.' =>
				array(
					'path' => 'my_ext-my_model',
					'read' => 'allow',
					'write' => 'allow'
				),
				'3.' =>
				array(
					'path' => 'my_secondext-*',
					'read' => 'deny',
					'write' => 'allow',
				)
			)
		);
		$configurationProvider->setSettings($settings);

		$request = new Request(NULL, 'my_ext-my_model/4/usergroup');
		$this->fixture->setRequest($request);
	}

	/**
	 * @test
	 */
	public function getDefaultConfigurationForPathTest() {
		$uri = 'my_ext-my_default_model/1/';
		$request = new Request(NULL, $uri);
		$testConfiguration = array(
			'path' => 'all',
			'read' => 'allow',
			'write' => 'deny',
		);
		$configuration = $this->fixture->getConfigurationForPath($request->path());
		$this->assertEquals($testConfiguration, $configuration);
	}

	/**
	 * @test
	 */
	public function getConfigurationForPathWithoutWildcardTest() {
		$uri = 'my_ext-my_model/3/';
		$request = new Request(NULL, $uri);
		$testConfiguration = array(
			'path' => 'my_ext-my_model',
			'read' => 'allow',
			'write' => 'allow'
		);
		$configuration = $this->fixture->getConfigurationForPath($request->path());
		$this->assertEquals($testConfiguration, $configuration);
	}

	/**
	 * @test
	 */
	public function getConfigurationForPathWithWildcardTest() {
		$uri = 'my_secondext-my_model/34/';
		$request = new Request(NULL, $uri);
		$testConfiguration = array(
			'path' => 'my_secondext-*',
			'read' => 'deny',
			'write' => 'allow'
		);
		$configuration = $this->fixture->getConfigurationForPath($request->path());
		$this->assertEquals($testConfiguration, $configuration);
	}

}
