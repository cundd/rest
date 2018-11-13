<?php

namespace Cundd\Rest\VirtualObject\Persistence\Exception;

class InvalidColumnNameException extends InvalidQueryException
{
    public static function assertValidColumnName($column, $message = '', $code = 0)
    {
        if (!ctype_alnum(str_replace('_', '', $column))) {
            throw new static($message ?: sprintf('The given column "%s" is not valid', $column), $code);
        }
    }
}
