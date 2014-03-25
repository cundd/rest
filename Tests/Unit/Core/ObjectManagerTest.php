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
