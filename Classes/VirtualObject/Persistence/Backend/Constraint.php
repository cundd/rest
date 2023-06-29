<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\OperatorInterface;

class Constraint implements OperatorInterface, ConstraintInterface
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
     * @var string
     */
    private $property;

    /**
     * Constraint constructor
     *
     * @param string     $property
     * @param int|string $operator
     * @param mixed      $value
     */
    public function __construct(string $property, $operator, $value)
    {
        $this->property = $property;
        $this->operator = WhereClauseBuilder::normalizeOperator($operator);
        $this->value = $value;
    }

    public static function equalTo($property, $value): self
    {
        return new static($property, self::OPERATOR_EQUAL_TO, $value);
    }

    public static function notEqualTo($property, $value): self
    {
        return new static($property, self::OPERATOR_NOT_EQUAL_TO, $value);
    }

    public static function lessThan($property, $value): self
    {
        return new static($property, self::OPERATOR_LESS_THAN, $value);
    }

    public static function lessThanOrEqualTo($property, $value): self
    {
        return new static($property, self::OPERATOR_LESS_THAN_OR_EQUAL_TO, $value);
    }

    public static function greaterThan($property, $value): self
    {
        return new static($property, self::OPERATOR_GREATER_THAN, $value);
    }

    public static function greaterThanOrEqualTo($property, $value): self
    {
        return new static($property, self::OPERATOR_GREATER_THAN_OR_EQUAL_TO, $value);
    }

    public static function like($property, $value): self
    {
        return new static($property, self::OPERATOR_LIKE, $value);
    }

    public static function contains($property, $value): self
    {
        return new static($property, self::OPERATOR_CONTAINS, $value);
    }

    public static function in($property, $value): self
    {
        return new static($property, self::OPERATOR_IN, $value);
    }

    public static function isNull($property): self
    {
        return new static($property, self::OPERATOR_IS_NULL, null);
    }

    public static function isEmpty($property): self
    {
        return new static($property, self::OPERATOR_IS_EMPTY, null);
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
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
