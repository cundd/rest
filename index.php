<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

require_once __DIR__ . '/Classes/Cundd/Rest/AutoloadDetector.php';
$autoloadDetector = new \Cundd\Rest\AutoloadDetector();
$autoloadDetector->registerAutoloader();

$bootstrap = new \Cundd\Rest\Bootstrap;
$bootstrap->init();
/** @var \Cundd\Rest\Dispatcher $dispatcher */
$dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Cundd\\Rest\\ObjectManager')
    ->get('Cundd\\Rest\\Dispatcher');
$dispatcher->dispatch();
