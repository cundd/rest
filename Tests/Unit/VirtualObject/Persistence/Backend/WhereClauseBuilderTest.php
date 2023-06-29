<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Exception\InvalidOperatorException;
use Cundd\Rest\VirtualObject\Persistence\Backend\WhereClause;
use Cundd\Rest\VirtualObject\Persistence\Backend\WhereClauseBuilder;
use Cundd\Rest\VirtualObject\Persistence\Query;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class WhereClauseBuilderTest extends TestCase
{
    /**
     * @var WhereClauseBuilder
     */
    private $fixture;

    public function testAddConstraint()
    {
        $this->fixture->addConstraint('property', 'value');

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '`property` = :property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['property' => 'value'],
            $where->getBoundVariables()
        );
    }

    public function testAddConstraintOr()
    {
        $this->fixture->addConstraint('property', 'value');
        $this->fixture->addConstraint('another_property', 200, null, null, '', QueryInterface::COMBINATOR_OR, null);
        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '`property` = :property OR `another_property` = :another_property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['property' => 'value', 'another_property' => 200],
            $where->getBoundVariables()
        );
    }

    public function testAddConstraintWithPrepareValues()
    {
        $this->fixture->addConstraint(
            'property',
            'value',
            function ($v) {
                return '--' . $v . '--';
            }
        );

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '`property` = :property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['property' => '--value--'],
            $where->getBoundVariables()
        );
    }

    public function testAddConstraintWithEscapeColumnName()
    {
        $this->fixture->addConstraint(
            'property',
            'value',
            null,
            function ($cn) {
                return '@' . $cn . '@';
            }
        );

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '@property@ = :property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['property' => 'value'],
            $where->getBoundVariables()
        );
    }

    public function testAddConstraintWithBindingPrefix()
    {
        $this->fixture->addConstraint(
            'property',
            'value',
            null,
            null,
            'pp_'
        );

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '`property` = :pp_property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['pp_property' => 'value'],
            $where->getBoundVariables()
        );
    }

    public function testGetWhere()
    {
        $this->assertEmptyWhere();
    }

    private function assertEmptyWhere()
    {
        $where = $this->fixture->getWhere();
        $this->assertInstanceOf(WhereClause::class, $where);
        $this->assertEquals('', $where->getExpression());
        $this->assertEmpty($where->getBoundVariables());
    }

    public function testBuild()
    {
        $this->fixture->addConstraints(
            ['property' => 'value', 'another_property' => 200]
        );

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '`property` = :property AND `another_property` = :another_property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['property' => 'value', 'another_property' => 200],
            $where->getBoundVariables()
        );

        $this->fixture->build(new Query());
        $this->assertEmptyWhere();
    }

    public function testAddConstraints()
    {
        $this->fixture->addConstraints(
            ['property' => 'value', 'another_property' => 200]
        );

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '`property` = :property AND `another_property` = :another_property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['property' => 'value', 'another_property' => 200],
            $where->getBoundVariables()
        );
    }

    public function testAddConstraintsWithCallbacks()
    {
        $this->fixture->addConstraints(
            ['property' => 'value', 'another_property' => 200],
            function ($v) {
                return '--' . $v . '--';
            },
            function ($cn) {
                return '@' . $cn . '@';
            }
        );

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '@property@ = :property AND @another_property@ = :another_property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['property' => '--value--', 'another_property' => '--200--'],
            $where->getBoundVariables()
        );
    }

    public function testAddConstraintsWithBindingPrefix()
    {
        $this->fixture->addConstraints(
            ['property' => 'value', 'another_property' => 200],
            null,
            null,
            'pp_'
        );

        $where = $this->fixture->getWhere();
        $this->assertEquals(
            '`property` = :pp_property AND `another_property` = :pp_another_property',
            $where->getExpression()
        );
        $this->assertEquals(
            ['pp_property' => 'value', 'pp_another_property' => 200],
            $where->getBoundVariables()
        );
    }

    public function testReset()
    {
        $this->fixture->addConstraints(['property' => 'value', 'another_property' => 200]);
        $this->fixture->reset();

        $this->assertEmptyWhere();
    }

    /**
     * @param string|int $input
     * @param            $expected
     * @dataProvider resolveOperatorDataProvider
     */
    public function testResolveOperator($input, string $expected)
    {
        $this->assertEquals($expected, WhereClauseBuilder::resolveOperator($input));
    }

    public function resolveOperatorDataProvider(): array
    {
        return [
            [QueryInterface::OPERATOR_IN, 'IN'],
            ['IN', 'IN'],
            ['in', 'IN'],
            [QueryInterface::OPERATOR_EQUAL_TO, '='],
            ['=', '='],
            ['==', '='],
            [QueryInterface::OPERATOR_NOT_EQUAL_TO, '!='],
            ['<>', '!='],
            ['!=', '!='],
            [QueryInterface::OPERATOR_LESS_THAN, '<'],
            ['<', '<'],
            [QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO, '<='],
            ['<=', '<='],
            [QueryInterface::OPERATOR_GREATER_THAN, '>'],
            ['>', '>'],
            [QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO, '>='],
            ['>=', '>='],
            [QueryInterface::OPERATOR_LIKE, 'LIKE'],
            ['LIKE', 'LIKE'],
            ['like', 'LIKE'],
        ];
    }

    /**
     * @param $input
     * @dataProvider resolveOperatorWithInvalidValuesDataProvider
     */
    public function testResolveOperatorWithInvalidValues($input)
    {
        $this->expectException(InvalidOperatorException::class);
        WhereClauseBuilder::resolveOperator($input);
    }

    public function resolveOperatorWithInvalidValuesDataProvider(): array
    {
        return [
            ['something else'],
            [[]],
            [new stdClass()],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = new WhereClauseBuilder();
    }

    protected function tearDown(): void
    {
        $this->fixture->reset();
        unset($this->fixture);
        parent::tearDown();
    }
}
