<?php


namespace Cundd\Rest\Tests\Functional\VirtualObject;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

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
        $query = [
            'uid' => 100,
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(1, $result);

        $query = [
            'content_time' => 1395678480,
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = [
            'content_time' => 1395678480,
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = [
            'content_time' => [
                'value'    => 1395678400,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = [
            'content_time' => [
                'value'    => 1395678480,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);

        $query = [
            'title' => [
                'doNotEscapeValue' => 'title',
                'value'            => "'Test entry' and content_time = '1395678480'",
            ],
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(2, $result);
    }


    /**
     * @test
     */
    public function getObjectDataByQuery()
    {
        $query = [
            'uid' => 100,
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals([self::$testData[0]], $result);

        $query = [
            'content_time' => 1395678480,
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = [
            'content_time' => 1395678480,
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = [
            'content_time' => [
                'value'    => 1395678400,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = [
            'content_time' => [
                'value'    => 1395678480,
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);

        $query = [
            'title' => [
                'doNotEscapeValue' => 'title',
                'value'            => "'Test entry' and content_time = '1395678480'",
            ],
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(self::$testData, $result);
    }


    /**
     * @test
     */
    public function getObjectCountByQueryWithZeroResult()
    {
        $query = [
            'uid' => time(),
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = [
            'content_time' => time(),
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = [
            'content_time' => time(),
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = [
            'content_time' => [
                'value'    => time(),
                'operator' => QueryInterface::OPERATOR_GREATER_THAN,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = [
            'content_time' => [
                'value'    => time(),
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);

        $query = [
            'title' => [
                'doNotEscapeValue' => 'title',
                'value'            => "'Test entry' and content_time = '" . time() . "'",
            ],
        ];
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query);
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     */
    public function getObjectDataByQueryWithEmptyResult()
    {
        $query = [
            'uid' => time(),
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = [
            'content_time' => time(),
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = [
            'content_time' => time(),
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = [
            'content_time' => [
                'value'    => 1395678400,
                'operator' => QueryInterface::OPERATOR_LESS_THAN,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = [
            'content_time' => [
                'value'    => time(),
                'operator' => QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

            ],
            'title'        => 'Test entry',
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);

        $query = [
            'title' => [
                'doNotEscapeValue' => 'title',
                'value'            => "'Test entry' and content_time = '" . time() . "'",
            ],
        ];
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, $query);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function addRow()
    {
        $newData = [
            'uid'          => null,
            'title'        => 'New test entry',
            'content'      => 'This is my third text',
            'content_time' => time(),
        ];
        $this->fixture->addRow(self::$testDatabaseTable, $newData);
        $query = [
            'content_time' => $newData['content_time'],
        ];

        $this->assertEquals(1, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query));
    }


    /**
     * @test
     */
    public function updateRow()
    {
        $newData = [
            'uid'   => 300,
            'title' => 'Changed test entry',
        ];

        $query = [
            'uid' => 100,
        ];
        $this->fixture->updateRow(self::$testDatabaseTable, $query, $newData);

        $this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $query));
    }


    /**
     * @test
     */
    public function removeRow()
    {
        $identifier = [
            'uid' => 200,
        ];
        $this->fixture->removeRow(self::$testDatabaseTable, $identifier);

        $this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, $identifier));
    }


    /**
     * @test
     */
    public function findAll()
    {
        $this->assertEquals(2, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, []));
    }
}
