<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Composer\Autoload\ClassLoader;
use RuntimeException;

abstract class AbstractBootstrap
{
    public function run(): void
    {
        $this->setupComposer();
        $this->bootstrapSystem();
    }

    private function setupComposer(): void
    {
        // If run within composer context
        if (class_exists(ClassLoader::class, false)) {
            return;
        }
        throw new RuntimeException('No suitable autoloader found');
        // // Load composer autoloader
        // if (file_exists(__DIR__ . '/../vendor/')) {
        //     require_once __DIR__ . '/../vendor/autoload.php';
        // } elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
        //     require_once __DIR__ . '/../../../autoload.php';
        // } elseif (file_exists(__DIR__ . '/../../../cundd_composer/Classes/Autoloader.php')) {
        //     if (!class_exists('Cundd\\CunddComposer\\Autoloader')) {
        //         require_once __DIR__ . '/../../../cundd_composer/Classes/Autoloader.php';
        //     }
        //     if (!class_exists('Cundd\\CunddComposer\\Utility\\GeneralUtility')) {
        //         require_once __DIR__ . '/../../../cundd_composer/Classes/Utility/GeneralUtility.php';
        //     }
        // } else {
        //     throw new RuntimeException('No suitable autoloader found');
        // }
    }

    /**
     * Bootstrap the testing system
     */
    abstract protected function bootstrapSystem(): void;
}
