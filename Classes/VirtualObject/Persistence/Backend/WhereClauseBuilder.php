<?php

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\InvalidOperatorException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidColumnNameException;
use Cundd\Rest\VirtualObject\Persistence\Query;
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
     * @param QueryInterface|array $query
     * @param callable|null        $prepareValue     `mixed function(mixed $queryValue)`
     * @param callable|null        $escapeColumnName `string function(string $propertyName)`
     * @param string               $bindingPrefix
     * @return self
     * @throws MissingConfigurationException
     */
    public function build($query, callable $prepareValue = null, callable $escapeColumnName = null, $bindingPrefix = '')
    {
        $this->reset();
        $configuration = null;

        if ($query instanceof Query && $query->getStatement()) {
            throw new \LogicException('`Query->getStatement()` is not supported by the Doctrine Backend');
        }
        if ($query instanceof QueryInterface) {
            $configuration = $query->getConfiguration();
            $query = $query->getConstraint();
        }

        return $this->addConstraints(
            $query,
            $prepareValue,
            $escapeColumnName,
            $bindingPrefix,
            QueryInterface::COMBINATOR_AND,
            $configuration
        );
    }

    /**
     * Add multiple constraints to the WHERE-clause
     *
     * @param array                       $query Map of property => value pairs to add constraints
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
        array $query,
        callable $prepareValue = null,
        callable $escapeColumnName = null,
        $bindingPrefix = '',
        $combinator = QueryInterface::COMBINATOR_AND,
        ConfigurationInterface $configuration = null
    ) {
        WhereClause::assertCombinator($combinator);
        foreach ($query as $property => $value) {
            $this->addConstraint(
                $property,
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
     * @param string                      $property
     * @param mixed                       $value
     * @param callable|null               $prepareValue     `mixed function(mixed $queryValue)`
     * @param callable|null               $escapeColumnName `string function(string $propertyName)`
     * @param string                      $bindingPrefix
     * @param string                      $combinator
     * @param ConfigurationInterface|null $configuration
     * @return WhereClauseBuilder
     * @throws InvalidColumnNameException
     * @throws InvalidOperatorException
     */
    public function addConstraint(
        $property,
        $value,
        callable $prepareValue = null,
        callable $escapeColumnName = null,
        $bindingPrefix = '',
        $combinator = QueryInterface::COMBINATOR_AND,
        ConfigurationInterface $configuration = null
    ) {
        WhereClause::assertCombinator($combinator);
        if ($configuration) {
            if (!$configuration->hasProperty($property)) {
                throw new InvalidColumnNameException('The given property is not defined', 1396092229);
            }
            $column = $configuration->getSourceKeyForProperty($property);
        } else {
            $column = $property;
        }

        InvalidColumnNameException::assertValidColumnName($column);

        if (is_scalar($value) || $value === null || is_object($value)) {
            $operator = '=';
            $comparisonValue = $value;
        } elseif (is_array($value)) {
            $operator = isset($value['operator']) ? $this->resolveOperator($value['operator']) : '=';
            $comparisonValue = $value['value'];
        } else {
            throw new InvalidOperatorException(
                sprintf(
                    'Operator could not be detected for type "%s"',
                    is_object($value) ? get_class($value) : gettype($value)
                ),
                1404821478
            );
        }

        if ($prepareValue === null) {
            $prepareValue = $this->getDefaultPrepareValueCallback();
        }
        if ($escapeColumnName === null) {
            $escapeColumnName = $this->getDefaultEscapeColumnNameCallback();
        }

        $bindingKey = $bindingPrefix . $column;
        $this->where->appendSql(
            $escapeColumnName($column)
            . ' ' . $operator . ' '
            . ':' . $bindingKey . '',
            $combinator
        );
        $this->where->bindVariable(
            $bindingKey,
            $prepareValue($comparisonValue)
        );

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
     */
    public function getWhere()
    {
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
        if (!is_scalar($operator)) {
            throw new InvalidOperatorException('Operator must be a scalar value', 1541074670);
        }
        switch ($operator) {
            case QueryInterface::OPERATOR_IN:
            case 'IN':
            case 'in':
                return 'IN';
            case QueryInterface::OPERATOR_EQUAL_TO:
            case '=':
            case '==':
                return '=';
            case QueryInterface::OPERATOR_NOT_EQUAL_TO:
            case '!=':
            case '<>':
                return '!=';
            case QueryInterface::OPERATOR_LESS_THAN:
            case '<':
                return '<';
            case QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO:
            case '<=':
                return '<=';
            case QueryInterface::OPERATOR_GREATER_THAN:
            case '>':
                return '>';
            case QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
            case '>=':
                return '>=';
            case QueryInterface::OPERATOR_LIKE:
            case 'LIKE':
            case 'like':
                return 'LIKE';
            default:
                throw new InvalidOperatorException('Unsupported operator encountered.', 1242816073);
        }
    }

    /**
     * @return \Closure
     */
    private function getDefaultPrepareValueCallback()
    {
        return function ($queryValue) {
            return $queryValue;
        };
    }

    /**
     * @return \Closure
     */
    private function getDefaultEscapeColumnNameCallback()
    {
        return function ($propertyName) {
            return '`' . $propertyName . '`';
        };
    }
}
