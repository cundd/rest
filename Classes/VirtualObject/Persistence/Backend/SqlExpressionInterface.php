<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

interface SqlExpressionInterface
{
    /**
     * Set the SQL expression string
     *
     * @param string|Parentheses $expression
     * @return self
     */
    public function setExpression($expression): self;

    /**
     * Append the string to the SQL expression
     *
     * @param string|Parentheses $expression
     * @param string|null        $combinator
     * @return self
     */
    public function appendSql($expression, ?string $combinator = QueryInterface::COMBINATOR_AND): self;

    /**
     * Return the expression as string
     *
     * @return string
     */
    public function getExpression(): string;

    /**
     * Return the dictionary of bound variables
     *
     * @return array
     */
    public function getBoundVariables(): array;

    /**
     * Bind the variable to the SQL WHERE-clause
     *
     * @param string           $key
     * @param string|int|float $value
     * @return self
     */
    public function bindVariable(string $key, $value): self;
}
