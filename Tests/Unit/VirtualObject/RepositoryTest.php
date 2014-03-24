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

class RepositoryTest extends AbstractDatabaseCase{
	/**
	 * @var \Cundd\Rest\VirtualObject\Persistence\RepositoryInterface
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\Persistence\\RepositoryInterface');
		$this->fixture->setConfiguration($this->getTestConfiguration());
		parent::setUp();
	}

	public function tearDown() {
		unset($this->fixture);
		parent::tearDown();
	}


	/**
	 * @test
	 */
	public function addTest() {
		$object = new VirtualObject(array());
		$this->fixture->add($object);
	}


	/**
	 * @test
	 */
	public function removeTest() {
		$object = new VirtualObject(array());
		$this->fixture->remove($object);
	}


	/**
	 * @test
	 */
	public function updateTest() {
		$object = new VirtualObject(array());
		$this->fixture->update($object);
	}


	/**
	 * @test
	 */
	public function findAllTest() {
		$this->fixture->findAll();
	}


	/**
	 * @test
	 */
	public function countAllTest() {
		$this->fixture->countAll();
	}


	/**
	 * @test
	 */
	public function removeAllTest() {
		$this->fixture->removeAll();
	}


	/**
	 * @test
	 */
	public function findByIdentifierTest() {
		$this->fixture->findByIdentifier($identifier);
	}
}
 