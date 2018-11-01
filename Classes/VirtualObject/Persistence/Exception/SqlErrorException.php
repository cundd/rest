<?php

namespace Cundd\Rest\VirtualObject\Persistence\Exception;

use Cundd\Rest\VirtualObject\Persistence\Exception;

/**
 * Exception thrown if a database query fails
 */
class SqlErrorException extends Exception
{
    /**
     * Return a new SQL error from the given exception
     *
     * @param \Exception $exception
     * @return SqlErrorException
     */
    public static function fromException(\Exception $exception)
    {
        return new static($exception->getMessage(), $exception->getCode(), $exception);
    }
}
