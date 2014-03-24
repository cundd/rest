<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 14:59
 */

namespace Cundd\Rest\VirtualObject\Exception;


use Cundd\Rest\VirtualObject\Exception;

/**
 * Exception thrown if the current property is not valid (i.e. it is not defined in the mapping)
 *
 * @package Cundd\Rest\VirtualObject\Exception
 */
class InvalidObjectException extends Exception {
}