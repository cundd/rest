<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Exception;

use Cundd\Rest\VirtualObject\Exception;

/**
 * Exception thrown if the current property is not valid (i.e. it is not defined in the mapping)
 */
class InvalidPropertyException extends Exception
{
}
