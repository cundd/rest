<?php


namespace Cundd\Rest\VirtualObject\Persistence\Backend;


use Cundd\Rest\VirtualObject\Persistence\BackendInterface;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidTableNameException;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

abstract class AbstractBackend implements BackendInterface
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
        $offset = (string)(int)$query->getOffset();
        if ($query->getLimit()) {
            return $offset . ',' . $query->getLimit();
        }

        return $offset;
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
        $orderArray = array_map(
            function ($property, $direction) {
                return $property . ' ' . $direction;
            },
            array_keys($orderings),
            $orderings
        );

        return implode(', ', $orderArray);
    }
}
