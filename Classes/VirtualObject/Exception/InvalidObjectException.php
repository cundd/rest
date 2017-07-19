<?php

namespace Cundd\Rest\VirtualObject\Exception;

use Cundd\Rest\VirtualObject\Exception;

/**
 * Exception thrown if the current property is not valid (i.e. it is not defined in the mapping)
 */
class InvalidObjectException extends Exception
{
}
