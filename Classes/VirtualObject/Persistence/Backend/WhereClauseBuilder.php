<?php

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\InvalidOperatorException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\Persistence\Backend\Doctrine\WhereClause;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidColumnNameException;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement;

class WhereClauseBuilder
{
    /**
     * Creates the WHERE-statement from the given key-value query-array
     *
     * @param QueryInterface|array $query
     * @param string               $bindingPrefix
     * @param callable|null        $prepareValue
     * @return WhereClause
     * @throws InvalidColumnNameException if one of the column names is invalid
     * @throws MissingConfigurationException
     */
    public function build($query, $bindingPrefix = '', callable $prepareValue = null)
    {
        $where = new WhereClause();
        $configuration = null;

        if ($query instanceof QueryInterface) {
            $configuration = $query->getConfiguration();

            $statement = $query->getStatement();
            if ($statement && $statement instanceof Statement) {
                return new WhereClause($statement->getStatement(), $statement->getBoundVariables());
            }

            $query = $query->getConstraint();
        }

        foreach ($query as $property => $value) {
            $this->processPair($where, $property, $value, $bindingPrefix, $prepareValue, $configuration);
        }

        return $where;
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
        switch ($operator) {
            case QueryInterface::OPERATOR_IN:
            case 'IN':
                return 'IN';
            case QueryInterface::OPERATOR_EQUAL_TO:
            case '=':
            case '==':
                return '=';
            case QueryInterface::OPERATOR_NOT_EQUAL_TO:
            case '!=':
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
                return 'LIKE';
            default:
                throw new InvalidOperatorException('Unsupported operator encountered.', 1242816073);
        }
    }

    /**
     * @param WhereClause            $where
     * @param string                 $property
     * @param mixed                  $value
     * @param string                 $bindingPrefix
     * @param callable               $prepareValue
     * @param ConfigurationInterface $configuration
     * @throws InvalidColumnNameException
     */
    private function processPair(
        $where,
        $property,
        $value,
        $bindingPrefix,
        $prepareValue,
        $configuration
    ) {
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

        $bindingKey = $bindingPrefix . $column;
        $where->appendSql(
            '`' . $column . '`'
            . ' ' . $operator . ' '
            . ':' . $bindingKey . ''
        );
        $where->bindVariable(
            $bindingKey,
            $prepareValue ? $prepareValue($comparisonValue) : $comparisonValue
        );
    }
}
