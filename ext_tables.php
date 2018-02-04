<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function ($extKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extKey,
            'Configuration/TypoScript/Page',
            'Virtual Object - Page'
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extKey,
            'Configuration/TypoScript/Content',
            'Virtual Object - Content'
        );


        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports'][$extKey]['handler'] = [
            'title' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:reports.handler.title',
            'description' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:reports.handler.description',
//            'icon' => 'EXT:' . $extKey . '/Resources/Public/Icons/tx_additionalreports_' . $report[1] . '.png',
            'report' => \Cundd\Rest\Documentation\HandlerReport::class,
        ];
    },
    $_EXTKEY
);