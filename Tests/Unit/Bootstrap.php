<?php
/**
 * Unit Test bootstrapping and legacy API abstraction
 */


if (file_exists(__DIR__ . '/../../vendor/')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    Tx_CunddComposer_Autoloader::register();
}

if (!class_exists('TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase')) {
    class_alias('\TYPO3\CMS\Core\Tests\UnitTestCase', '\TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase');
}
