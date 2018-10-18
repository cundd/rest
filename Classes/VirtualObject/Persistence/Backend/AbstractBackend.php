<?php


namespace Cundd\Rest\VirtualObject\Persistence\Backend;


use Cundd\Rest\VirtualObject\Persistence\BackendInterface;
use Cundd\Rest\VirtualObject\Persistence\Exception\InvalidTableNameException;

abstract class AbstractBackend implements BackendInterface
{
    /**
     * Checks if the given table name is valid
     *
     * @param string $tableName
     * @throws InvalidTableNameException
     */
    protected function checkTableArgument($tableName)
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
}
