<?php

namespace Cundd\Rest\VirtualObject\Persistence\Exception;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

class InvalidOrderingException extends InvalidQueryException
{
    public static function assertValidOrdering($direction)
    {
        if (strtoupper($direction) !== QueryInterface::ORDER_ASCENDING
            && strtoupper($direction) !== QueryInterface::ORDER_DESCENDING
        ) {
            throw new static('Invalid ordering direction');
        }
    }
}
