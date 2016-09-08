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

use Cundd\Rest\VirtualObject\VirtualObject;

require_once __DIR__ . '/AbstractDatabaseCase.php';

class PersistenceManagerTest extends AbstractDatabaseCase
{
    /**
     * @var \Cundd\Rest\VirtualObject\Persistence\PersistenceManagerInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\Persistence\\PersistenceManagerInterface');
        $this->fixture->setConfiguration($this->getTestConfiguration());
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findAllTest()
    {
        $result = $this->fixture->getObjectDataByQuery(array());
        $result = $this->getTestDataFromObjectCollection($result);
        $this->assertEquals(self::$testData, $result);
    }

    /**
     * @test
     */
    public function countAllTest()
    {
        $this->assertEquals(2, $this->fixture->getObjectCountByQuery(array()));
    }

    /**
     * @test
     */
    public function addTest()
    {
        $newObjectData = array(
            'uid' => 900,
            'title' => 'My new title',
            'content' => 'A new test entry',
            'contentTime' => time()
        );
        $object = new VirtualObject($newObjectData);

        $this->fixture->add($object);


        $this->assertEquals(3, $this->fixture->getObjectCountByQuery(array()));

        $result = $this->fixture->getObjectDataByQuery(array());
        $result = $this->getTestDataFromObjectCollection($result);

        $newObjectData['content_time'] = $newObjectData['contentTime'];
        unset($newObjectData['contentTime']);
        $testData = self::$testData;
        $testData[] = $newObjectData;
        $this->assertEquals($testData, $result);
    }

    /**
     * @test
     */
    public function removeTest()
    {
        $objectData = array(
            'uid' => 100, // <= this is relevant
            'title' => 'My new title',
            'content' => 'A new test entry',
            'contentTime' => time()
        );

        $object = new VirtualObject($objectData);
        $this->fixture->remove($object);

        $this->assertEquals(1, $this->fixture->getObjectCountByQuery(array()));


        $objectData = array(
            'uid' => 200, // <= this is relevant
        );

        $object = new VirtualObject($objectData);
        $this->fixture->remove($object);

        $this->assertEquals(0, $this->fixture->getObjectCountByQuery(array()));
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $objectData = array(
            'uid' => 100, // <= this is relevant
            'title' => 'My new title',
            'content' => 'A new test entry',
            'contentTime' => time()
        );

        $object = new VirtualObject($objectData);
        $this->fixture->update($object);

        $result = $this->fixture->getObjectByIdentifier($objectData['uid']);
        $this->assertInstanceOf('Cundd\\Rest\\VirtualObject\\VirtualObject', $result);
        $this->assertEquals($object->getData(), $result->getData());
    }

    /**
     * @test
     */
    public function findByIdentifierTest()
    {
        $uid = 100;
        $result = $this->fixture->getObjectByIdentifier($uid);

        $this->assertInstanceOf('Cundd\\Rest\\VirtualObject\\VirtualObject', $result);

        $resultData = $result->getData();
        $this->assertEquals($uid, $resultData['uid']);

        $this->assertNull($this->fixture->getObjectByIdentifier(time()));
    }

    /**
     * @param array <VirtualObject> $collection
     * @return array
     */
    protected function getTestDataFromObjectCollection($collection)
    {
        $newCollection = array();
        foreach ($collection as $item) {
            $newCollection[] = $this->getTestDataFromObject($item);
        }
        return $newCollection;
    }

    /**
     * @param VirtualObject $virtualObject
     * @return array
     */
    protected function getTestDataFromObject($virtualObject)
    {
        $virtualObjectData = $virtualObject->getData();
        $virtualObjectData['content_time'] = $virtualObjectData['contentTime'];
        unset($virtualObjectData['contentTime']);
        return $virtualObjectData;
    }
}
