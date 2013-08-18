<?php

namespace Cundd\Rest\Test\Core;
use Cundd\Rest\Request;

class ConfigurationBasedAuthenticationProviderTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Cundd\Rest\Authentication\ConfigurationBasedAuthenticationProvider
	 */
	protected $fixture;

	public function setUp() {
		\Tx_CunddComposer_Autoloader::register();
		$this->fixture = $this->objectManager->get('Cundd\\Rest\\Authentication\\ConfigurationBasedAuthenticationProvider');

		$settings = array(
			"paths" =>
			array(
				"1." =>
				array(
					"path" => "all",
					"read" => "allow",
					"write" => "deny",
				),
				"2." =>
				array(
					"path" => "my_ext-my_model",
					"read" => "allow",
					"write" => "allow"
				),
				"3." =>
				array(
					"path" => "my_secondext-*",
					"read" => "allow",
					"write" => "deny",
				)
			)
		);
		$this->fixture->setSettings($settings);

		$request = new Request(NULL, 'my_ext-my_model/4/usergroup');
		$this->fixture->setRequest($request);
	}

	/**
	 * @test
	 */
	public function getDefaultConfigurationForPathTest() {
		$path = 'my_ext-my_default_model/1/';
		$testConfiguration = array(
			"path" => "all",
			"read" => "allow",
			"write" => "deny",
		);
		$configuration = $this->fixture->getConfigurationForPath($path);
		$this->assertEquals($testConfiguration, $configuration);
	}

	/**
	 * @test
	 */
	public function getConfigurationForPathWithoutWildcardTest() {
		$path = 'my_ext-my_model/3/';
		$testConfiguration = array(
			"path" => "my_ext-my_model",
			"read" => "allow",
			"write" => "allow"
		);
		$configuration = $this->fixture->getConfigurationForPath($path);
		$this->assertEquals($testConfiguration, $configuration);
	}

	/**
	 * @test
	 */
	public function getConfigurationForPathWithWildcardTest() {
		$path = 'my_secondext-my_model/34/';
		$testConfiguration = array(
			"path" => "my_secondext-*",
			"read" => "allow",
			"write" => "deny"
		);
		$configuration = $this->fixture->getConfigurationForPath($path);
		$this->assertEquals($testConfiguration, $configuration);
	}

	/**
	 * @test
	 */
	public function throwBadConfigurationExceptionTest() {
		$this->setExpectedException('Cundd\\Rest\\Authentication\\Exception\\InvalidConfigurationException');
		$settings = array(
			"paths" =>
			array(
				"1." =>
				array(
					"path" => "all",
					// "read" => NULL,
					"write" => "deny",
				),
			)
		);
		$this->fixture->setSettings($settings);
		$success = $this->fixture->authenticate();
		$this->assertFalse($success);
	}

}
