<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

(static function () {
    ExtensionManagementUtility::addStaticFile(
        'custom_rest',
        'Configuration/TypoScript',
        'Custom Rest Configuration'
    );
})();
