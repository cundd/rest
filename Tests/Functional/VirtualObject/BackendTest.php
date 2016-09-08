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

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 16:11
 */

namespace Cundd\Rest\Tests\Functional\VirtualObject;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

require_once __DIR__ . '/AbstractDatabaseCase.php';

class BackendTest extends AbstractDatabaseCase
{
    /**
     * @var \Cundd\Rest\VirtualObject\Persistence\BackendInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\Persistence\\BackendInterface');
//		$this->fixture->setConfiguration($this->getTestConfiguration());
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getObjectCountByQuery()
    {
        $query = array(
            'uid' => 100
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(1, $result);

        $query = array(
            'content_time' => 1395678480
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = array(
            'content_time' => 1395678480,
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = array(
            'content_time' => array(
                'value' => 1395678400,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = array(
            'content_time' => array(
                'value' => 1395678480,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = array(
            'title' => array(
                'doNotEscapeValue' => 'title',
                'value' => "'Test entry' and content_time = '1395678480'",
            ),
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);
    }


    /**
     * @test
     */
    public function getObjectDataByQuery()
    {
        $query = array(
            'uid' => 100
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(array(self::$testData[0]), $result);

        $query = array(
            'content_time' => 1395678480
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = array(
            'content_time' => 1395678480,
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = array(
            'content_time' => array(
                'value' => 1395678400,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = array(
            'content_time' => array(
                'value' => 1395678480,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = array(
            'title' => array(
                'doNotEscapeValue' => 'title',
                'value' => "'Test entry' and content_time = '1395678480'",
            ),
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);
    }


    /**
     * @test
     */
    public function getObjectCountByQueryWithZeroResult()
    {
        $query = array(
            'uid' => time()
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = array(
            'content_time' => time()
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = array(
            'content_time' => time(),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = array(
            'content_time' => array(
                'value' => time(),
                'operator' => QueryInterface::OPERATOR_GREATER_THAN,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = array(
            'content_time' => array(
                'value' => time(),
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = array(
            'title' => array(
                'doNotEscapeValue' => 'title',
                'value' => "'Test entry' and content_time = '" . time() . "'",
            ),
        );
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     */
    public function getObjectDataByQueryWithEmptyResult()
    {
        $query = array(
            'uid' => time()
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = array(
            'content_time' => time()
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = array(
            'content_time' => time(),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = array(
            'content_time' => array(
                'value' => 1395678400,
                'operator' => QueryInterface::OPERATOR_LESS_THAN,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = array(
            'content_time' => array(
                'value' => time(),
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ),
            'title' => 'Test entry'
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = array(
            'title' => array(
                'doNotEscapeValue' => 'title',
                'value' => "'Test entry' and content_time = '" . time() . "'",
            ),
        );
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function addRow()
    {
        $newData = array(
            'uid' => null,
            'title' => 'New test entry',
            'content' => 'This is my third text',
            'content_time' => time()
        );
        $this->fixture->addRow(self::$testDatabaseTable, $newData);
        $query = array(
            'content_time' => $newData['content_time'],
        );

        $this->assertEquals(1, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query));
    }


    /**
     * @test
     */
    public function updateRow()
    {
        $newData = array(
            'uid' => 300,
            'title' => 'Changed test entry',
        );

        $query = array(
            'uid' => 100,
        );
        $this->fixture->updateRow(self::$testDatabaseTable, $query, $newData);

        $this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query));
    }


    /**
     * @test
     */
    public function removeRow()
    {
        $identifier = array(
            'uid' => 200,
        );
        $this->fixture->removeRow(self::$testDatabaseTable, $identifier);

        $this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $identifier));
    }


    /**
     * @test
     */
    public function findAll()
    {
        $this->assertEquals(2, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, array()));
    }
}
