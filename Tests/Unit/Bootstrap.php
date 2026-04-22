<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit;

class Bootstrap
{
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
define('ORIGINAL_ROOT', __DIR__ . '/../../.Build/Web/');
$bootstrap = new Bootstrap();
unset($bootstrap);
