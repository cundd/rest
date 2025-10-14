<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Exception;

use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

class InvalidOrderingException extends InvalidQueryException
{
    public static function assertValidOrdering($direction)
    {
        if (QueryInterface::ORDER_ASCENDING !== strtoupper($direction)
            && QueryInterface::ORDER_DESCENDING !== strtoupper($direction)
        ) {
            throw new static('Invalid ordering direction');
        }
    }
}
