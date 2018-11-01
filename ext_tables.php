<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        if (version_compare(TYPO3_version, '9.5.0') >= 0) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
                'rest',
                'Configuration/TypoScript/Page/TYPO3-9',
                'Virtual Object - Page'
            );
        } else {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
                'rest',
                'Configuration/TypoScript/Page/TYPO3-8',
                'Virtual Object - Page'
            );
        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'rest',
            'Configuration/TypoScript/Content',
            'Virtual Object - Content'
        );


        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['rest']['handler'] = [
            'title'       => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.title',
            'description' => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.description',
//            'icon' => 'EXT:rest/Resources/Public/Icons/tx_additionalreports_' . $report[1] . '.png',
            'report'      => \Cundd\Rest\Documentation\HandlerReport::class,
        ];
    }
);