<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\InvalidOperatorException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidColumnNameException;
use Cundd\Rest\VirtualObject\Persistence\Exception\WhereClauseException;
use Cundd\Rest\VirtualObject\Persistence\OperatorInterface;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

/**
 * Class to build WHERE-clauses
 */
class WhereClauseBuilder
{
    /**
     * @var WhereClause
     */
    private $where;

    /**
     * The current level of nested opened parentheses
     *
     * @var int
     */
    private $openedParenthesesLevel = 0;

    /**
     * WHERE-Clause builder
     */
    public function __construct()
    {
        $this->where = new WhereClause();
    }

    /**
     * Creates a new WHERE-clause from the given query
     *
     * The current WHERE-clause will be reset
     *
     * @param QueryInterface $query
     * @param callable|null  $prepareValue     `mixed function(mixed $queryValue)`
     * @param callable|null  $escapeColumnName `string function(string $propertyName)`
     * @param string         $bindingPrefix
     * @return self
     * @throws MissingConfigurationException
     * @throws InvalidColumnNameException
     */
    public function build(
        QueryInterface $query,
        callable $prepareValue = null,
        callable $escapeColumnName = null,
        string $bindingPrefix = ''
    ) {
        $this->reset();

        return $this->addConstraints(
            $query->getConstraint(),
            $prepareValue,
            $escapeColumnName,
            $bindingPrefix,
            QueryInterface::COMBINATOR_AND,
            $query->getConfiguration()
        );
    }

    /**
     * Add multiple constraints to the WHERE-clause
     *
     * @param array                       $constraints Map of property => value pairs to add constraints
     * @param callable|null               $prepareValue
     * @param callable|null               $escapeColumnName
     * @param string                      $bindingPrefix
     * @param string                      $combinator
     * @param ConfigurationInterface|null $configuration
     * @return $this
     * @throws InvalidColumnNameException
     * @throws InvalidOperatorException
     */
    public function addConstraints(
        array $constraints,
        callable $prepareValue = null,
        callable $escapeColumnName = null,
        string $bindingPrefix = '',
        string $combinator = QueryInterface::COMBINATOR_AND,
        ConfigurationInterface $configuration = null
    ) {
        WhereClause::assertCombinator($combinator);
        foreach ($constraints as $property => $value) {
            $this->addConstraint(
                is_int($property) ? (string)$property : $property,
                $value,
                $prepareValue,
                $escapeColumnName,
                $bindingPrefix,
                $combinator,
                $configuration
            );
        }

        return $this;
    }

    /**
     * Add a constraint to the WHERE-clause
     *
     * @param string                                            $property
     * @param int|float|string|array|ConstraintInterface|object $value
     * @param callable|null                                     $prepareValue     `mixed function(mixed $queryValue)`
     * @param callable|null                                     $escapeColumnName `string function(string $propertyName)`
     * @param string                                            $bindingPrefix
     * @param string                                            $combinator
     * @param ConfigurationInterface|null                       $configuration
     * @return WhereClauseBuilder
     * @throws InvalidColumnNameException
     * @throws InvalidOperatorException
     */
    public function addConstraint(
        string $property,
        $value,
        callable $prepareValue = null,
        callable $escapeColumnName = null,
        string $bindingPrefix = '',
        string $combinator = QueryInterface::COMBINATOR_AND,
        ConfigurationInterface $configuration = null
    ) {
        WhereClause::assertCombinator($combinator);

        if ($value instanceof Constraint) {
            $property = $value->getProperty();
        }

        if ($configuration) {
            if (!$configuration->hasProperty($property)) {
                throw new InvalidColumnNameException('The given property is not defined', 1396092229);
            }
            $column = $configuration->getSourceKeyForProperty($property);
        } else {
            $column = $property;
        }

        if ($value instanceof LogicalAnd || $value instanceof LogicalOr) {
            $subCombinator = $value instanceof LogicalAnd ? QueryInterface::COMBINATOR_AND : QueryInterface::COMBINATOR_OR;

            return $this->openParentheses($combinator)
                ->addConstraints(
                    $value->getConstraints(),
                    $prepareValue,
                    $escapeColumnName,
                    $bindingPrefix . '_' . uniqid(),
                    $subCombinator,
                    $configuration
                )
                ->closeParentheses();
        }

        InvalidColumnNameException::assertValidColumnName($column);
        list('operator' => $operator, 'value' => $comparisonValue) = $this->extractOperatorAndValue($value);

        if ($prepareValue === null) {
            $prepareValue = $this->getDefaultPrepareValueCallback();
        }
        if ($escapeColumnName === null) {
            $escapeColumnName = $this->getDefaultEscapeColumnNameCallback();
        }

        if ($operator === OperatorInterface::OPERATOR_IN) {
            $this->addInConstraint(
                $prepareValue($comparisonValue),
                $column,
                $bindingPrefix,
                $combinator,
                $escapeColumnName
            );
        } else {
            $bindingKey = $bindingPrefix . $column;
            $this->appendSql(
                $escapeColumnName($column)
                . ' ' . $this->resolveOperator($operator) . ' '
                . ':' . $bindingKey . '',
                $combinator
            );
            $this->bindVariable(
                $bindingKey,
                $prepareValue($comparisonValue)
            );
        }

        return $this;
    }

    /**
     * Insert opening parentheses into the SQL query
     *
     * @param string $combinator
     * @return $this
     */
    public function openParentheses(string $combinator = QueryInterface::COMBINATOR_AND)
    {
        $this->openedParenthesesLevel += 1;
        $this->where->appendSql(Parentheses::open(), $combinator);

        return $this;
    }

    /**
     * Insert closing parentheses into the SQL query
     *
     * @return $this
     */
    public function closeParentheses()
    {
        $this->openedParenthesesLevel -= 1;
        $this->where->appendSql(Parentheses::close(), null);

        return $this;
    }

    /**
     * Reset the WHERE-clause
     *
     * @return self
     */
    public function reset()
    {
        $this->where = new WhereClause();

        return $this;
    }

    /**
     * Return the configured WHERE-clause
     *
     * @return WhereClause
     * @throws WhereClauseException if parentheses are opened that are not closed
     */
    public function getWhere()
    {
        if ($this->openedParenthesesLevel > 0) {
            throw new WhereClauseException(sprintf('Detected %d unclosed parentheses', $this->openedParenthesesLevel));
        }

        return $this->where;
    }

    /**
     * Returns the SQL operator for the given operator
     *
     * @param string $operator One of the OPERATOR_* constants
     * @throws InvalidOperatorException
     * @return string an SQL operator
     */
    public static function resolveOperator($operator)
    {
        switch (static::normalizeOperator($operator)) {
            case QueryInterface::OPERATOR_IN:
                return 'IN';
            case QueryInterface::OPERATOR_EQUAL_TO:
                return '=';
            case QueryInterface::OPERATOR_NOT_EQUAL_TO:
                return '!=';
            case QueryInterface::OPERATOR_LESS_THAN:
                return '<';
            case QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO:
                return '<=';
            case QueryInterface::OPERATOR_GREATER_THAN:
                return '>';
            case QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
                return '>=';
            case QueryInterface::OPERATOR_LIKE:
                return 'LIKE';
            default:
                throw new \OutOfRangeException('Unsupported operator encountered. Normalization failed', 1242816074);
        }
    }

    /**
     * Returns the SQL operator constant for the given operator
     *
     * @param string $operator One of the OPERATOR_* constants
     * @throws InvalidOperatorException
     * @return int One of the OPERATOR_* constants
     */
    public static function normalizeOperator($operator): int
    {
        if (!is_scalar($operator)) {
            throw new InvalidOperatorException(
                sprintf(
                    'Operator must be a scalar value, "%s" given',
                    is_object($operator) ? get_class($operator) : gettype($operator)
                ),
                1541074670
            );
        }
        switch ($operator) {
            case 'IN':
            case 'in':
            case QueryInterface::OPERATOR_IN:
                return QueryInterface::OPERATOR_IN;
            case '=':
            case '==':
            case QueryInterface::OPERATOR_EQUAL_TO:
                return QueryInterface::OPERATOR_EQUAL_TO;
            case '!=':
            case '<>':
            case QueryInterface::OPERATOR_NOT_EQUAL_TO:
                return QueryInterface::OPERATOR_NOT_EQUAL_TO;
            case '<':
            case QueryInterface::OPERATOR_LESS_THAN:
                return QueryInterface::OPERATOR_LESS_THAN;
            case '<=':
            case QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO:
                return QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO;
            case '>':
            case QueryInterface::OPERATOR_GREATER_THAN:
                return QueryInterface::OPERATOR_GREATER_THAN;
            case '>=':
            case QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
                return QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO;
            case 'LIKE':
            case 'like':
            case QueryInterface::OPERATOR_LIKE:
                return QueryInterface::OPERATOR_LIKE;
            default:
                throw new InvalidOperatorException('Unsupported operator encountered.', 1242816073);
        }
    }

    /**
     * Append the string to the SQL WHERE-clause
     *
     * @param string $clause
     * @param string $combinator
     * @return $this
     */
    protected function appendSql($clause, $combinator = QueryInterface::COMBINATOR_AND)
    {
        $this->where->appendSql($clause, $combinator);

        return $this;
    }

    /**
     * Bind the variable to the SQL WHERE-clause
     *
     * @param string           $key
     * @param string|int|float $value
     * @return $this
     */
    protected function bindVariable($key, $value)
    {
        $this->where->bindVariable($key, $value);

        return $this;
    }

    /**
     * @return \Closure
     */
    protected function getDefaultPrepareValueCallback()
    {
        return function ($queryValue) {
            return $queryValue;
        };
    }

    /**
     * @return \Closure
     */
    protected function getDefaultEscapeColumnNameCallback()
    {
        return function ($propertyName) {
            return '`' . $propertyName . '`';
        };
    }

    /**
     * @param $value
     * @return array
     */
    private function extractOperatorAndValue($value): array
    {
        if ($value instanceof Constraint) {
            return [
                'operator' => $this->normalizeOperator($value->getOperator()),
                'value'    => $value->getValue(),
            ];
        }

        if (is_scalar($value) || $value === null || is_object($value)) {
            return [
                'operator' => OperatorInterface::OPERATOR_EQUAL_TO,
                'value'    => $value,
            ];
        }

        if (is_array($value)) {
            $operator = isset($value['operator'])
                ? $this->normalizeOperator($value['operator'])
                : OperatorInterface::OPERATOR_EQUAL_TO;

            return [
                'operator' => $operator,
                'value'    => $value['value'],
            ];
        }

        throw new InvalidOperatorException(
            sprintf(
                'Operator could not be detected for type "%s"',
                is_object($value) ? get_class($value) : gettype($value)
            ),
            1404821478
        );
    }

    /**
     * @param array       $values
     * @param string|null $column
     * @param string      $bindingPrefix
     * @param string      $combinator
     * @param callable    $escapeColumnName
     * @return WhereClauseBuilder
     */
    private function addInConstraint(
        array $values,
        string $column,
        string $bindingPrefix,
        string $combinator,
        callable $escapeColumnName
    ): self {
        $bindingKeyBase = ':' . $bindingPrefix . $column;

        $hasOnlyIntegerValues = count(array_filter($values, 'is_int')) === count($values);
        if ($hasOnlyIntegerValues) {
            return $this->appendSql(
                $escapeColumnName($column)
                . ' ' . $this->resolveOperator(OperatorInterface::OPERATOR_IN) . ' '
                . '(' . implode(',', array_values($values)) . ')',
                $combinator
            );
        }

        $i = 0;
        $bindings = [];
        foreach ($values as $value) {
            $currentKey = $bindingKeyBase . $i;
            $bindings[$currentKey] = $value;

            $this->bindVariable($currentKey, $value);
            $i += 1;
        }

        return $this->appendSql(
            $escapeColumnName($column)
            . ' ' . $this->resolveOperator(OperatorInterface::OPERATOR_IN) . ' '
            . '(' . implode(',', array_keys($bindings)) . ')',
            $combinator
        );
    }
}
