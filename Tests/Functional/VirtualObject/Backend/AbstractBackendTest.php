<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\VirtualObject\Backend;

use Cundd\Rest\Tests\Functional\VirtualObject\AbstractDatabaseCase;
use Cundd\Rest\VirtualObject\Persistence\Backend\Constraint;
use Cundd\Rest\VirtualObject\Persistence\Backend\LogicalAnd;
use Cundd\Rest\VirtualObject\Persistence\BackendInterface;
use Cundd\Rest\VirtualObject\Persistence\OperatorInterface;
use Cundd\Rest\VirtualObject\Persistence\Query;

abstract class AbstractBackendTest extends AbstractDatabaseCase
{
    /**
     * @var BackendInterface
     */
    protected $fixture;

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider objectCountByQueryDataProvider
     * @param array $query
     * @param int   $expected
     */
    public function getObjectCountByQuery(array $query, $expected)
    {
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, new Query($query));
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider objectCountByQueryDataProvider
     * @param array $query
     * @param int   $expected
     */
    public function getObjectCountByQueryWithConstraint(array $query, $expected)
    {
        $result = $this->fixture->getObjectCountByQuery(
            self::$testDatabaseTable,
            new Query(
                array_map(
                    function ($q, $property) {
                        if (is_array($q)) {
                            return new Constraint($property, $q['operator'], $q['value']);
                        } else {
                            return Constraint::equalTo($property, $q);
                        }
                    },
                    $query,
                    array_keys($query)
                )
            )
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider objectDataByQueryDataProvider
     * @param array $query
     * @param array $expected
     */
    public function getObjectDataByQuery(array $query, array $expected)
    {
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, new Query($query));
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider objectDataByQueryDataProvider
     * @param array $query
     * @param array $expected
     */
    public function getObjectDataByQueryWithConstraints(array $query, array $expected)
    {
        $result = $this->fixture->getObjectDataByQuery(
            self::$testDatabaseTable,
            new Query(
                array_map(
                    function ($q, $property) {
                        if (is_array($q)) {
                            return new Constraint($property, $q['operator'], $q['value']);
                        } else {
                            return Constraint::equalTo($property, $q);
                        }
                    },
                    $query,
                    array_keys($query)
                )
            )
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider objectDataByQueryDataProvider
     * @param array $query
     * @param array $expected
     */
    public function getObjectDataByQueryWithLogicConstraint(array $query, array $expected)
    {
        $result = $this->fixture->getObjectDataByQuery(
            self::$testDatabaseTable,
            new Query(
                new LogicalAnd(
                    array_map(
                        function ($q, $property) {
                            if (is_array($q)) {
                                return new Constraint($property, $q['operator'], $q['value']);
                            } else {
                                return Constraint::equalTo($property, $q);
                            }
                        },
                        $query,
                        array_keys($query)
                    )
                )
            )
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @dataProvider emptyResultQueryDataProvider
     * @param array $query
     */
    public function getObjectCountByQueryWithZeroResult(array $query)
    {
        $result = $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, new Query($query));
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     * @dataProvider emptyResultQueryDataProvider
     * @param array $query
     */
    public function getObjectDataByQueryWithEmptyResult(array $query)
    {
        $result = $this->fixture->getObjectDataByQuery(self::$testDatabaseTable, new Query($query));
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

        $this->assertEquals(1, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, new Query($query)));
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
        $this->assertEquals(1, $this->fixture->updateRow(self::$testDatabaseTable, $query, $newData));

        // Record with UID `100` should not exist anymore
        $this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, new Query($query)));
    }

    /**
     * @test
     */
    public function removeRow()
    {
        $identifier = [
            'uid' => 200,
        ];
        $this->assertEquals(1, $this->fixture->removeRow(self::$testDatabaseTable, $identifier));

        // Record with UID `200` should not exist anymore
        $this->assertEquals(0, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, new Query($identifier)));
    }


    /**
     * @test
     */
    public function findAll()
    {
        $this->assertEquals(2, $this->fixture->getObjectCountByQuery(self::$testDatabaseTable, new Query()));
    }

    public function emptyResultQueryDataProvider()
    {
        return [
            [
                [
                    'uid' => time(),
                ],
            ],
            [
                [
                    'content_time' => time(),
                ],
            ],
            [
                [
                    'content_time' => time(),
                    'title'        => 'Test entry',
                ],
            ],
            [
                [
                    'content_time' => [
                        'value'    => time(),
                        'operator' => OperatorInterface::OPERATOR_GREATER_THAN,

                    ],
                    'title'        => 'Test entry',
                ],
            ],
            [
                [
                    'content_time' => [
                        'value'    => time(),
                        'operator' => OperatorInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,

                    ],
                    'title'        => 'Test entry',
                ],
            ],
        ];
    }

    public function objectCountByQueryDataProvider()
    {
        return array_map(
            function ($pair) {
                return [
                    $pair[0],
                    count($pair[1]),
                ];
            },
            $this->objectDataByQueryDataProvider()
        );
    }

    public function objectDataByQueryDataProvider()
    {
        return [
            [
                [
                    'uid' => 100,
                ],
                [self::$testData[0]],
            ],
            [
                [
                    'content_time' => 1395678480,
                ],
                self::$testData,
            ],
            [
                [
                    'content_time' => 1395678480,
                    'title'        => 'Test entry',
                ],
                self::$testData,
            ],
            [
                [
                    'content_time' => [
                        'value'    => 1395678400,
                        'operator' => OperatorInterface::OPERATOR_GREATER_THAN,
                    ],
                    'title'        => 'Test entry',
                ],
                self::$testData,
            ],
            [
                [
                    'content_time' => [
                        'value'    => 1395678480,
                        'operator' => OperatorInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,
                    ],
                    'title'        => 'Test entry',
                ],
                self::$testData,
            ],
            [
                [
                    'uid' => [
                        'value'    => [100, 200],
                        'operator' => OperatorInterface::OPERATOR_IN,
                    ],
                ],
                self::$testData,
            ],
        ];
    }
}
