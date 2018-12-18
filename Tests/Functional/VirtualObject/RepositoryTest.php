<?php
declare(strict_types=1);


namespace Cundd\Rest\Tests\Functional\VirtualObject;

use Cundd\Rest\VirtualObject\Persistence\RepositoryInterface;
use Cundd\Rest\VirtualObject\VirtualObject;

require_once __DIR__ . '/AbstractDatabaseCase.php';

class RepositoryTest extends AbstractDatabaseCase
{
    /**
     * @var RepositoryInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = $this->objectManager->get(RepositoryInterface::class);
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
        $result = $this->fixture->findAll();
        $result = $this->getTestDataFromObjectCollection($result);
        $this->assertEquals(self::$testData, $result);
    }

    /**
     * @test
     */
    public function countAllTest()
    {
        $this->assertEquals(2, $this->fixture->countAll());
    }

    /**
     * @test
     */
    public function addTest()
    {
        $newObjectData = [
            'uid'         => 900,
            'title'       => 'My new title',
            'content'     => 'A new test entry',
            'contentTime' => time(),
        ];
        $object = new VirtualObject($newObjectData);

        $this->fixture->add($object);


        $this->assertEquals(3, $this->fixture->countAll());

        $result = $this->fixture->findAll();
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
        $objectData = [
            'uid'         => 100, // <= this is relevant
            'title'       => 'My new title',
            'content'     => 'A new test entry',
            'contentTime' => time(),
        ];

        $object = new VirtualObject($objectData);
        $this->fixture->remove($object);

        $this->assertEquals(1, $this->fixture->countAll());


        $objectData = [
            'uid' => 200, // <= this is relevant
        ];

        $object = new VirtualObject($objectData);
        $this->fixture->remove($object);

        $this->assertEquals(0, $this->fixture->countAll());
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $objectData = [
            'uid'         => 100, // <= this is relevant
            'title'       => 'My new title',
            'content'     => 'A new test entry',
            'contentTime' => time(),
        ];

        $object = new VirtualObject($objectData);
        $this->fixture->update($object);

        $result = $this->fixture->findByIdentifier($objectData['uid']);
        $this->assertInstanceOf(VirtualObject::class, $result);
        $this->assertEquals($object->getData(), $result->getData());
    }

    /**
     * @test
     */
    public function removeAllTest()
    {
        $countBefore = $this->fixture->countAll();
        $this->fixture->removeAll();
        $countAfter = $this->fixture->countAll();

        $this->assertNotEquals($countBefore, $countAfter);
        $this->assertEquals(0, $countAfter);
        $this->assertEquals(2, $countBefore);
    }

    /**
     * @test
     */
    public function findByIdentifierTest()
    {
        $uid = 100;
        $result = $this->fixture->findByIdentifier($uid);

        $this->assertInstanceOf(VirtualObject::class, $result);

        $resultData = $result->getData();
        $this->assertEquals($uid, $resultData['uid']);

        $this->assertNull($this->fixture->findByIdentifier(time()));
    }

    /**
     * @param VirtualObject[] $collection
     * @return array
     */
    protected function getTestDataFromObjectCollection($collection)
    {
        $newCollection = [];
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
