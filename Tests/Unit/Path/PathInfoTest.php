<?php
/*
 *  Copyright notice
 *
 *  (c) 2016 Daniel Corn <info@cundd.net>, cundd
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

namespace Cundd\Rest\Tests\Unit\Path;

use Cundd\Rest\Path\PathInfo;

require_once __DIR__ . '/../../Bootstrap.php';

/**
 * Test case for \Cundd\Rest\Path\PathUtility
 *
 * @author Daniel Corn <info@cundd.net>
 */
class PathInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Cundd\Rest\Path\PathInfo
     */
    private $fixture;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new PathInfo('Cundd', 'MyExtension', 'ModelPath');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getVendorTest()
    {
        $this->assertSame('Cundd', $this->fixture->getVendor());
    }

    /**
     * @test
     */
    public function getExtensionTest()
    {
        $this->assertSame('MyExtension', $this->fixture->getExtension());
    }

    /**
     * @test
     */
    public function getModelTest()
    {
        $this->assertSame('ModelPath', $this->fixture->getModel());
    }

    /**
     * @test
     */
    public function offsetGetVendorTest()
    {
        $this->assertSame('Cundd', $this->fixture[0]);
    }

    /**
     * @test
     */
    public function offsetGetExtensionTest()
    {
        $this->assertSame('MyExtension', $this->fixture[1]);
    }

    /**
     * @test
     */
    public function offsetGetModelTest()
    {
        $this->assertSame('ModelPath', $this->fixture[2]);
    }

    /**
     * @test
     */
    public function listTest()
    {
        list($vendor, $extension, $model) = $this->fixture;
        $this->assertSame($vendor, 'Cundd');
        $this->assertSame($extension, 'MyExtension');
        $this->assertSame($model, 'ModelPath');
    }

    /**
     * @test
     * @expectedException \OutOfRangeException
     */
    public function offsetGetOutOfBoundTest()
    {
        $this->fixture[3];
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function offsetSetNotAllowed()
    {
        $this->fixture[1] = '';
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function offsetUnsetNotAllowed()
    {
        unset($this->fixture[1]);
    }
}
