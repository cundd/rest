<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

#\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'rest');
$TYPO3_CONF_VARS['FE']['eID_include']['rest'] = 'EXT:rest/index.php';