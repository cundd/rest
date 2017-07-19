<?php

namespace Cundd\Rest\Tests\Unit;


class Bootstrap
{
    public function bootstrapSystem()
    {
        $this->setupComposer();
    }

    private function setupComposer()
    {
        // Load composer autoloader
        if (file_exists(__DIR__ . '/../../vendor/')) {
            require_once __DIR__ . '/../../vendor/autoload.php';
        } else {
            if (!class_exists('Cundd\\CunddComposer\\Autoloader')) {
                require_once __DIR__ . '/../../../cundd_composer/Classes/Autoloader.php';
            }
            if (!class_exists('Cundd\\CunddComposer\\Utility\\GeneralUtility')) {
                require_once __DIR__ . '/../../../cundd_composer/Classes/Utility/GeneralUtility.php';
            }
            \Cundd\CunddComposer\Autoloader::register();
        }
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new Bootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
