<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\Exception\WhereClauseException;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

abstract class AbstractSqlExpression implements SqlExpressionInterface
{
    /**
     * @var string[]
     */
    private $expressionParts = [];

    /**
     * @var mixed[]
     */
    private $boundVariables;

    /**
     * Expression constructor
     *
     * @param string $sqlExpression
     * @param array  $boundVariables
     */
    public function __construct(string $sqlExpression = '', array $boundVariables = [])
    {
        $this->setExpression($sqlExpression);
        $this->boundVariables = $boundVariables;
    }

    public function setExpression($expression): SqlExpressionInterface
    {
        $this->assertExpression($expression);
        if (trim($expression) !== '') {
            $this->expressionParts = [$expression];
        } else {
            $this->expressionParts = [];
        }

        return $this;
    }

    public function appendSql(
        $expression,
        ?string $combinator = QueryInterface::COMBINATOR_AND
    ): SqlExpressionInterface {
        $this->assertExpression($expression);
        if (null !== $combinator) {
            $this->assertCombinator($combinator);
        }

        $lastExpressionPart = end($this->expressionParts);
        $parenthesesOpen = Parentheses::open();
        $addCombinator = $combinator && $lastExpressionPart
            && (
                $lastExpressionPart !== $parenthesesOpen
                || (is_string($lastExpressionPart)
                    && substr((string)$lastExpressionPart, -1) !== (string)$parenthesesOpen)
            );
        if ($addCombinator) {
            $this->expressionParts[] = strtoupper($combinator);
        }
        $this->expressionParts[] = $expression;

        return $this;
    }

    public function getExpression(): string
    {
        return implode(' ', $this->expressionParts);
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
     * @throws WhereClauseException
     */
    public static function assertCombinator($combinator)
    {
        if (!is_string($combinator)) {
            throw new WhereClauseException(
                sprintf(
                    'Logical combinator must be of type string, \'%s\' given',
                    is_object($combinator) ? get_class($combinator) : gettype($combinator)
                )
            );
        }
        if (!in_array(strtoupper($combinator), [QueryInterface::COMBINATOR_AND, QueryInterface::COMBINATOR_OR])) {
            throw new WhereClauseException(
                sprintf(
                    'Logical combinator must be either \'%s\' or \'%s\'',
                    QueryInterface::COMBINATOR_AND,
                    QueryInterface::COMBINATOR_OR
                )
            );
        }
    }

    /**
     * @param $expression
     * @throws WhereClauseException
     */
    private function assertExpression($expression)
    {
        if (!is_string($expression) && !($expression instanceof Parentheses)) {
            throw new WhereClauseException(
                sprintf(
                    'Argument "expression" must be of type string, or %s, \'%s\' given',
                    Parentheses::class,
                    is_object($expression) ? get_class($expression) : gettype($expression)
                )
            );
        }
    }
}
