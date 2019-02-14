<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\OperatorInterface;

class Constraint implements OperatorInterface
{
    /**
     * @var string|int
     */
    private $operator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Constraint constructor
     *
     * @param int|string $operator
     * @param mixed      $value
     */
    public function __construct($operator, $value)
    {
        $this->operator = WhereClauseBuilder::normalizeOperator($operator);
        $this->value = $value;
    }

    public static function equalTo($value): self
    {
        return new static(self::OPERATOR_EQUAL_TO, $value);
    }

    public static function notEqualTo($value): self
    {
        return new static(self::OPERATOR_NOT_EQUAL_TO, $value);
    }

    public static function lessThan($value): self
    {
        return new static(self::OPERATOR_LESS_THAN, $value);
    }

    public static function lessThanOrEqualTo($value): self
    {
        return new static(self::OPERATOR_LESS_THAN_OR_EQUAL_TO, $value);
    }

    public static function greaterThan($value): self
    {
        return new static(self::OPERATOR_GREATER_THAN, $value);
    }

    public static function greaterThanOrEqualTo($value): self
    {
        return new static(self::OPERATOR_GREATER_THAN_OR_EQUAL_TO, $value);
    }

    public static function like($value): self
    {
        return new static(self::OPERATOR_LIKE, $value);
    }

    public static function contains($value): self
    {
        return new static(self::OPERATOR_CONTAINS, $value);
    }

    public static function in($value): self
    {
        return new static(self::OPERATOR_IN, $value);
    }

    public static function isNull(): self
    {
        return new static(self::OPERATOR_IS_NULL, null);
    }

    public static function isEmpty(): self
    {
        return new static(self::OPERATOR_IS_EMPTY, null);
    }

    /**
     * @return int|string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
