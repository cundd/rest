<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

#require_once __DIR__ . '/vendor/autoload.php';
//if (TYPO3_version) {}
Tx_CunddComposer_Autoloader::register();

//spl_autoload_register(function ($className) {
//	$ds = DIRECTORY_SEPARATOR;
//	$pathSubparts = explode('\\', $className);
//	if (count($pathSubparts) < 2) {
//		$pathSubparts = explode('_', $className);
//	}
//	if (count($pathSubparts) < 3) {
//		return;
//	}
//
//	$relativePath = lcfirst($pathSubparts[2]) . $ds
//		. 'Classes' . $ds
//		. implode($ds, array_slice($pathSubparts, 3))
//		. '.php';
//#	$relativePath = str_replace(array('\\', '_'), $ds, $relativePath) . '.php';
//	if (file_exists(__DIR__ . '/../../../typo3/sysext/' . $relativePath)) {
//		require_once __DIR__ . '/../../../typo3/sysext/' . $relativePath;
//	}
//});


$bootstrap = new \Cundd\Rest\Bootstrap;
$bootstrap->init();
$app = new \Cundd\Rest\App;
$app->dispatch();
?>