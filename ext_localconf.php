<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

#\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'rest');
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['rest'] = 'EXT:rest/index.php';

if (isset($_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], 0, 6) === '/rest/') {
	$_GET['eID'] = 'rest';
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'] = array();
}

if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Cundd\\Rest\\Command\\RestCommandController';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['rest'] = array(
		'EXT:' . $_EXTKEY . '/server_typo3.php',
		'_CLI_rest',
	);
}