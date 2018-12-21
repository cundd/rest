<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

abstract class AbstractSqlExpression implements SqlExpressionInterface
{
    private $expression = '';
    private $boundVariables = [];

    /**
     * Expression constructor
     *
     * @param string $sqlExpression
     * @param array  $boundVariables
     */
    public function __construct(string $sqlExpression = '', array $boundVariables = [])
    {
        $this->expression = $sqlExpression;
        $this->boundVariables = $boundVariables;
    }

    public function setExpression(string $expression): SqlExpressionInterface
    {
        $this->expression = $expression;

        return $this;
    }

    public function appendSql(
        string $expression,
        string $combinator = QueryInterface::COMBINATOR_AND
    ): SqlExpressionInterface {
        $this->assertCombinator($combinator);

        if ($this->expression) {
            $this->expression .= ' ' . strtoupper($combinator) . ' ' . $expression;
        } else {
            $this->expression = $expression;
        }

        return $this;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function getBoundVariables(): array
    {
        return $this->boundVariables;
    }

    public function bindVariable(string $key, $value): SqlExpressionInterface
    {
        $this->boundVariables[$key] = $value;

        return $this;
    }

    public function __toString()
    {
        return $this->getExpression();
    }

    /**
     * @param $combinator
     */
    public static function assertCombinator($combinator)
    {
        if (!is_string($combinator)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Logical combinator must be of type string, \'%s\' given',
                    is_object($combinator) ? get_class($combinator) : gettype($combinator)
                )
            );
        }
        if (!in_array(strtoupper($combinator), [QueryInterface::COMBINATOR_AND, QueryInterface::COMBINATOR_OR])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Logical combinator must be either \'%s\' or \'%s\'',
                    QueryInterface::COMBINATOR_AND,
                    QueryInterface::COMBINATOR_OR
                )
            );
        }
    }
}
