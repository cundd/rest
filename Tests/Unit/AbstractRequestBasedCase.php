<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 29.12.16
 * Time: 12:38
 */

namespace Cundd\Rest\Tests\Unit;


use Cundd\Rest\Tests\RequestBuilderTrait;
use PHPUnit_Framework_TestCase;

abstract class AbstractRequestBasedCase extends PHPUnit_Framework_TestCase
{
    use RequestBuilderTrait;
}
