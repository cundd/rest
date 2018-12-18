<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit;


use Cundd\Rest\Tests\AbstractBootstrap;

require_once __DIR__ . '/../AbstractBootstrap.php';

class Bootstrap extends AbstractBootstrap
{
    protected function bootstrapSystem()
    {
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new Bootstrap();
$bootstrap->run();
unset($bootstrap);
