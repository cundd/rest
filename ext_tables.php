<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['rest']['handler'] = [
            'title'       => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.title',
            'description' => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.description',
            // 'icon' => 'EXT:rest/Resources/Public/Icons/tx_additionalreports_' . $report[1] . '.png',
            'report'      => \Cundd\Rest\Documentation\HandlerReport::class,
        ];
    }
);