<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

#\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'rest');
$TYPO3_CONF_VARS['FE']['eID_include']['rest'] = 'EXT:rest/index.php';

if (isset($_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], 0, 6) === '/rest/') {
	$_GET['eID'] = 'rest';
}

if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'] = array();
}

if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Cundd\\Rest\\Command\\RestCommandController';
}
