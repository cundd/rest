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
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 12:27
 */

namespace Cundd\Rest\Test\VirtualObject;

use Cundd\Rest\VirtualObject\Configuration;
use Cundd\Rest\VirtualObject\VirtualObject;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

require_once __DIR__ . '/../AbstractCase.php';

/**
 * Virtual Object tests
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class VirtualObjectTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \Cundd\Rest\VirtualObject\VirtualObject
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new VirtualObject(array(
			'firstName' => 'Daniel',
			'lastName' => 'Corn',
			'age' => 27
		));
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getTest() {
		$this->assertEquals('Daniel', $this->fixture->valueForKey('firstName'));
		$this->assertEquals('Corn', $this->fixture->valueForKey('lastName'));
	}

	/**
	 * @test
	 */
	public function setTest() {
		$this->fixture->setValueForKey('firstName', 'Steve');
		$this->fixture->setValueForKey('lastName', 'Jobs');

		$this->assertEquals('Steve', $this->fixture->valueForKey('firstName'));
		$this->assertEquals('Jobs', $this->fixture->valueForKey('lastName'));
	}
}
