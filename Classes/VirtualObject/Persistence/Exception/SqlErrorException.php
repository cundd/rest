<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Exception;

use Cundd\Rest\VirtualObject\Persistence\Exception;
use Throwable;

/**
 * Exception thrown if a database query fails
 */
class SqlErrorException extends Exception
{
    /**
     * Return a new SQL error from the given exception
     *
     * @param Throwable $exception
     * @return SqlErrorException
     */
    public static function fromException(Throwable $exception): self
    {
        return new static($exception->getMessage(), $exception->getCode(), $exception);
    }
}
