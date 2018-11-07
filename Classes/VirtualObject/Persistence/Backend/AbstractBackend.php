<?php


namespace Cundd\Rest\VirtualObject\Persistence\Backend;


use Cundd\Rest\VirtualObject\Persistence\BackendInterface;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidColumnNameException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidOrderingException;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidTableNameException;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;
use Cundd\Rest\VirtualObject\Persistence\RawQueryBackendInterface;

abstract class AbstractBackend implements BackendInterface, RawQueryBackendInterface
{
    /**
     * Checks if the given table name is valid
     *
     * @param string $tableName
     * @throws InvalidTableNameException
     */
    protected function assertValidTableName($tableName)
    {
        if (!is_string($tableName)) {
            throw new InvalidTableNameException(
                'The given table name is of type ' . gettype($tableName) . '. You may have a wrong argument order',
                1395677889
            );
        }
        if (!$tableName) {
            throw new InvalidTableNameException('The given table name is empty', 1395677890);
        }
        if (!ctype_alnum(str_replace('_', '', $tableName))) {
            throw new InvalidTableNameException('The given table name is not valid', 1395682370);
        }
    }

    /**
     * Returns the offset and limit statement for the given query
     *
     * @param QueryInterface $query
     * @return string
     */
    protected function createLimitStatementFromQuery(QueryInterface $query)
    {
        $offset = (int)$query->getOffset();
        $limit = (int)$query->getLimit();
        if ($limit > 0) {
            return $offset . ',' . $limit;
        }

        if ($offset > 0) {
            throw new \LogicException(
                'Queries with offset but without limit are not implemented'
            );
        }

        return '';
    }

    /**
     * Returns the order by statement for the given query
     *
     * @param QueryInterface $query
     * @return string
     */
    protected function createOrderingStatementFromQuery(QueryInterface $query)
    {
        $orderings = $query->getOrderings();
        if (!$orderings) {
            return '';
        }
        $orderArray = array_map(
            function ($property, $direction) {
                InvalidColumnNameException::assertValidColumnName($property);
                InvalidOrderingException::assertValidOrdering($direction);

                return $property . ' ' . $direction;
            },
            array_keys($orderings),
            $orderings
        );

        return implode(', ', $orderArray);
    }

    /**
     * Return if the query is empty
     *
     * @param array|QueryInterface $query
     * @return bool
     */
    protected function isQueryEmpty($query)
    {
        if ($query instanceof QueryInterface) {
            return empty($query->getConstraint());
        } else {
            return empty($query);
        }
    }
}
