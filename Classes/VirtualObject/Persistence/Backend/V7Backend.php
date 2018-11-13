<?php

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Exception\InvalidOperatorException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidColumnNameException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidTableNameException;
use Cundd\Rest\VirtualObject\Persistence\Exception\SqlErrorException;
use Cundd\Rest\VirtualObject\Persistence\Query;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException as Typo3SqlErrorException;

class V7Backend extends AbstractBackend
{
    /**
     * @var DatabaseConnection
     */
    private $connection;

    /**
     * V7Backend constructor
     *
     * @param DatabaseConnection $connection
     */
    public function __construct(DatabaseConnection $connection)
    {
        $this->connection = $connection;
    }

    public function addRow($tableName, array $row)
    {
        $this->assertValidTableName($tableName);

        try {
            $this->getConnection()->exec_INSERTquery($tableName, $row);
            $uid = $this->getConnection()->sql_insert_id();
        } catch (Typo3SqlErrorException $exception) {
            throw SqlErrorException::fromException($exception);
        }
        $this->checkNonExceptionSqlErrors();

        return (integer)$uid;
    }

    public function updateRow($tableName, array $identifier, array $row)
    {
        $this->assertValidTableName($tableName);
        try {
            $result = $this->getConnection()->exec_UPDATEquery(
                $tableName,
                $this->createWhereStatementFromQuery($identifier, $tableName),
                $row
            );
        } catch (Typo3SqlErrorException $exception) {
            throw SqlErrorException::fromException($exception);
        }
        $this->checkNonExceptionSqlErrors();

        return $result;
    }

    public function removeRow($tableName, array $identifier)
    {
        $this->assertValidTableName($tableName);
        try {
            $result = $this->getConnection()->exec_DELETEquery(
                $tableName,
                $this->createWhereStatementFromQuery($identifier, $tableName)
            );
        } catch (Typo3SqlErrorException $exception) {
            throw SqlErrorException::fromException($exception);
        }
        $this->checkNonExceptionSqlErrors();

        return $result;
    }

    public function getObjectCountByQuery($tableName, $query)
    {
        $this->assertValidTableName($tableName);
        try {
            list($row) = $this->getConnection()->exec_SELECTgetRows(
                'COUNT(*) AS count',
                $tableName,
                $this->createWhereStatementFromQuery($query, $tableName)
            );
        } catch (Typo3SqlErrorException $exception) {
            throw SqlErrorException::fromException($exception);
        }
        $this->checkNonExceptionSqlErrors();

        return intval($row['count']);
    }

    public function getObjectDataByQuery($tableName, $query)
    {
        $this->assertValidTableName($tableName);
        try {
            $result = $this->getConnection()->exec_SELECTgetRows(
                '*',
                $tableName,
                $this->createWhereStatementFromQuery($query, $tableName),
                '',
                $query instanceof QueryInterface ? $this->createOrderingStatementFromQuery($query) : '',
                $query instanceof QueryInterface ? $this->createLimitStatementFromQuery($query) : ''
            );
        } catch (Typo3SqlErrorException $exception) {
            throw SqlErrorException::fromException($exception);
        }
        $this->checkNonExceptionSqlErrors();

        return $result;
    }

    public function executeQuery($query)
    {
        $databaseConnection = $this->getConnection();
        try {
            $result = $databaseConnection->sql_query($query);
        } catch (Typo3SqlErrorException $exception) {
            throw SqlErrorException::fromException($exception);
        }
        $this->checkNonExceptionSqlErrors();

        return $result;
    }

    /**
     * Creates the WHERE-statement from the given key-value query-array
     *
     * @param QueryInterface|array $query
     * @param string               $tableName
     * @return string
     * @throws InvalidColumnNameException if one of the column names is invalid
     * @throws InvalidOperatorException
     * @throws InvalidTableNameException if the table name is invalid
     * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException
     * @throws Exception
     */
    protected function createWhereStatementFromQuery($query, $tableName)
    {
        $configuration = null;
        $this->assertValidTableName($tableName);

        if ($query instanceof QueryInterface) {
            $configuration = $query->getConfiguration();

            if ($query instanceof Query) {
                $statement = $query->getStatement();
                if ($statement && $statement instanceof Statement) {
                    $sql = (string)$statement->getStatement();
                    $parameters = $statement->getBoundVariables();

                    return $this->replacePlaceholders($sql, $parameters, $tableName);
                }
            }

            $query = $query->getConstraint();
        }

        $adapter = $this->getConnection();
        $constraints = [];
        foreach ($query as $property => $value) {
            if ($configuration && !$configuration->hasProperty($property)) {
                throw new InvalidColumnNameException('The given property is not defined', 1396092229);
            }

            $column = $configuration ? $configuration->getSourceKeyForProperty($property) : $property;
            InvalidColumnNameException::assertValidColumnName($column);

            if (is_scalar($value) || $value === null) {
                $operator = '=';
                $comparisonValue = $adapter->fullQuoteStr($value, $tableName);
            } elseif (is_array($value)) {
                /**
                 * If you don't want the given value to be escaped set the constraint's "doNotEscapeValue" key to the
                 * name of it's property key
                 *
                 * Example:
                 * Use the raw value for the property "dangerousValue"
                 *
                 * $constraints = array(
                 *        "dangerousValue" => array(
                 *            "value" => "a raw unescaped value",
                 *            "doNotEscapeValue" => "dangerousValue"
                 *        )
                 * );
                 */
                if (isset($value['doNotEscapeValue']) && $value['doNotEscapeValue'] === $property) {
                    $comparisonValue = $value['value'];
                } else {
                    $comparisonValue = $adapter->fullQuoteStr($value['value'], $tableName);
                }
                $operator = isset($value['operator']) ? $this->resolveOperator($value['operator']) : '=';
            } else {
                throw new InvalidOperatorException('Operator could not be detected', 1404821478);
            }
            $constraints[] = ''
                . $column
                . $operator
                . $comparisonValue;
        }

        return implode(' AND ', $constraints);
    }

    /**
     * Replace query placeholders in a query part by the given parameters
     *
     * @param string &$sqlString The query part with placeholders
     * @param array  $parameters The parameters
     * @param string $tableName
     *
     * @throws Exception
     * @return string
     */
    protected function replacePlaceholders(&$sqlString, array $parameters, $tableName = 'foo')
    {
        if (substr_count($sqlString, '?') !== count($parameters)) {
            throw new Exception(
                'The number of question marks to replace must be equal to the number of parameters.',
                1242816074
            );
        }
        $adapter = $this->getConnection();
        $offset = 0;
        foreach ($parameters as $parameter) {
            $markPosition = strpos($sqlString, '?', $offset);
            if ($markPosition !== false) {
                if ($parameter === null) {
                    $parameter = 'NULL';
                } elseif (is_array($parameter)
                    || $parameter instanceof \ArrayAccess
                    || $parameter instanceof \Traversable
                ) {
                    $items = [];
                    foreach ($parameter as $item) {
                        $items[] = $adapter->fullQuoteStr($item, $tableName);
                    }
                    $parameter = '(' . implode(',', $items) . ')';
                } else {
                    $parameter = $adapter->fullQuoteStr($parameter, $tableName);
                }
                $sqlString = substr($sqlString, 0, $markPosition)
                    . $parameter
                    . substr($sqlString, ($markPosition + 1));
            }
            $offset = $markPosition + strlen($parameter);
        }

        return $sqlString;
    }

    /**
     * Returns the SQL operator for the given JCR operator type.
     *
     * @param string $operator One of the JCR_OPERATOR_* constants
     * @throws InvalidOperatorException
     * @return string an SQL operator
     */
    protected function resolveOperator($operator)
    {
        return WhereClauseBuilder::resolveOperator($operator);
    }

    /**
     * Returns the database adapter
     *
     * @return DatabaseConnection
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Checks if there are SQL errors in the last query, and if yes, throw an exception.
     *
     * @return void
     * @throws SqlErrorException
     */
    protected function checkNonExceptionSqlErrors()
    {
        $databaseConnection = $this->getConnection();
        $error = $databaseConnection->sql_error();
        if ($error !== '') {
            throw new SqlErrorException($error, $databaseConnection->sql_errno());
        }
    }
}
