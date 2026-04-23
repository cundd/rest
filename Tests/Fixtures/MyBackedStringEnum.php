<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

enum MyBackedStringEnum: string
{
    case A = 'Case A';
    case B = 'Case B';
    case C = 'Case C';
}
