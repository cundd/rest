<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use PHPUnit\Framework\TestCase;

abstract class AbstractBootstrap
{
    public function run()
    {
        $this->setupComposer();
        $this->bootstrapSystem();
        $this->setupPHPUnitFallback();
    }

    private function setupComposer()
    {
        // Load composer autoloader
        if (file_exists(__DIR__ . '/../vendor/')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        } elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
            require_once __DIR__ . '/../../../autoload.php';
        } elseif (file_exists(__DIR__ . '/../../../cundd_composer/Classes/Autoloader.php')) {
            if (!class_exists('Cundd\\CunddComposer\\Autoloader')) {
                require_once __DIR__ . '/../../../cundd_composer/Classes/Autoloader.php';
            }
            if (!class_exists('Cundd\\CunddComposer\\Utility\\GeneralUtility')) {
                require_once __DIR__ . '/../../../cundd_composer/Classes/Utility/GeneralUtility.php';
            }
            \Cundd\CunddComposer\Autoloader::register();
        } else {
            throw new \RuntimeException('No suitable autoloader found');
        }
    }

    /**
     * Bootstrap the testing system
     */
    abstract protected function bootstrapSystem();

    private function setupPHPUnitFallback()
    {
        if (!class_exists(TestCase::class, true)) {
            class_alias('PHPUnit_Framework_TestCase', TestCase::class);
        }
    }
}
