<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Fixtures;

use LogicException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends LogicException implements NotFoundExceptionInterface
{
}
