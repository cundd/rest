<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function (): void {
    // Register Cache
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])
        || !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'] = [];
    }

    ExtensionManagementUtility::addTypoScriptSetup("@import 'EXT:rest/Configuration/TypoScript/setup.typoscript'");
})();
