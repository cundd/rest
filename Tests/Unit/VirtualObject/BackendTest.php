<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 16:11
 */

namespace Cundd\Rest\Test\VirtualObject;

use Cundd\Rest\VirtualObject\VirtualObject;

require_once __DIR__ . '/AbstractDatabaseCase.php';

class BackendTest extends AbstractDatabaseCase {
	/**
	 * @var \Cundd\Rest\VirtualObject\Persistence\BackendInterface
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\Persistence\\BackendInterface');
//		$this->fixture->setConfiguration($this->getTestConfiguration());
		parent::setUp();
	}

	public function tearDown() {
		unset($this->fixture);
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function getObjectCountByQuery() {
		$query  = array(
			'uid' => 100
		);
		$result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
		$this->assertEquals(1, $result);

		$query  = array(
			'content_time' => 1395678480
		);
		$result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
		$this->assertEquals(2, $result);

		$query  = array(
			'content_time' => 1395678480,
			'title' => 'Test entry'
		);
		$result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
		$this->assertEquals(2, $result);
	}


	/**
	 * @test
	 */
	public function getObjectDataByQuery() {
		$query  = array(
			'uid' => 100
		);
		$result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
		$this->assertEquals(array(self::$testData[0]), $result);

		$query  = array(
			'content_time' => 1395678480
		);
		$result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
		$this->assertEquals(self::$testData, $result);

		$query  = array(
			'content_time' => 1395678480,
			'title' => 'Test entry'
		);
		$result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
		$this->assertEquals(self::$testData, $result);
	}

	/**
	 * @test
	 */
	public function addRow() {
		$newData = array(
			'uid'          => NULL,
			'title'        => 'New test entry',
			'content'      => 'This is my third text',
			'content_time' => time()
		);
		$this->fixture->addRow(self::$testDatabaseTable, $newData);
		$query  = array(
			'content_time' => $newData['content_time'],
		);

		$this->assertEquals(1, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query));
	}


	/**
	 * @test
	 */
	public function updateRow() {
		$newData = array(
			'uid'          => 300,
			'title'        => 'Changed test entry',
		);

		$query  = array(
			'uid' => 100,
		);
		$this->fixture->updateRow(self::$testDatabaseTable, $query, $newData);

		$this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query));
	}


	/**
	 * @test
	 */
	public function removeRow() {
		$identifier  = array(
			'uid' => 200,
		);
		$this->fixture->removeRow(self::$testDatabaseTable, $identifier);

		$this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $identifier));
	}


	/**
	 * @test
	 */
	public function findAll() {
		$this->assertEquals(2, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, array()));
	}
}
 