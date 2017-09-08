<?php


namespace Cundd\Rest\Tests;


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

    /**
     * Bootstrap the testing system
     */
    abstract protected function bootstrapSystem();

    private function setupPHPUnitFallback()
    {
        spl_autoload_register(
            function ($className) {
                $trimmedClassName = trim($className, '\\');
                if ($trimmedClassName === 'PHPUnit\\Framework\\TestCase') {
                    class_alias('PHPUnit\\Framework\\TestCase', 'PHPUnit_Framework_TestCase');
                }
                if ($trimmedClassName === 'PHPUnit_Framework_TestCase') {
                    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\\Framework\\TestCase');
                }
            }
        );
    }
}
