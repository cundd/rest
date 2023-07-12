<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function () {
    ExtensionManagementUtility::addStaticFile(
        'rest',
        'Configuration/TypoScript/Page',
        'Virtual Object - Page'
    );
    
    ExtensionManagementUtility::addStaticFile(
        'rest',
        'Configuration/TypoScript/Content',
        'Virtual Object - Content'
    );
})();
