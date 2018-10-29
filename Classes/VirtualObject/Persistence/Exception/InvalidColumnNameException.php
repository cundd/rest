<?php

namespace Cundd\Rest\VirtualObject\Persistence\Exception;

use Cundd\Rest\VirtualObject\Persistence\Exception;

class InvalidColumnNameException extends Exception
{
    public static function assertValidColumnName($column, $message = '', $code = 0)
    {
        if (!ctype_alnum(str_replace('_', '', $column))) {
            throw new static($message ?: 'The given column is not valid', $code);
        }
    }
}
