<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (file_exists(__DIR__ . '/vendor/')) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	\Cundd\CunddComposer\Autoloader::register();
}

if (!class_exists('Cundd\\Rest\\Bootstrap')) {
	throw new RuntimeException('Could not find class \\Cundd\\Rest\\Bootstrap. '
		. 'Maybe the Composer dependencies have not been installed',
		1397921464
	);
}

$bootstrap = new \Cundd\Rest\Bootstrap;
$bootstrap->init();
/** @var \Cundd\Rest\Dispatcher $dispatcher */
$dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Cundd\\Rest\\ObjectManager')->get('Cundd\\Rest\\Dispatcher');
$dispatcher->dispatch();
