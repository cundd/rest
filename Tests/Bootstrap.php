<?php
/**
 * Unit Test bootstrapping
 */

if (file_exists(__DIR__ . '/../vendor/')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    \Cundd\CunddComposer\Autoloader::register();
}
