<?php

namespace Cundd\Rest\Tests\Unit;


use Cundd\Rest\Tests\RequestBuilderTrait;
use PHPUnit\Framework\TestCase;

abstract class AbstractRequestBasedCase extends TestCase
{
    use RequestBuilderTrait;
}
