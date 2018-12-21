<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

interface SqlExpressionInterface
{
    /**
     * Set the SQL expression string
     *
     * @param string $expression
     * @return self
     */
    public function setExpression(string $expression): self;

    /**
     * Append the string to the SQL expression
     *
     * @param string $expression
     * @param string $combinator
     * @return self
     */
    public function appendSql(string $expression, string $combinator = QueryInterface::COMBINATOR_AND): self;

    /**
     * @return string
     */
    public function getExpression(): string;

    /**
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