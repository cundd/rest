<?php

use Cundd\Rest\Documentation\HandlerReport;

defined('TYPO3') or die();

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['rest']['handler'] = [
            'title'       => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.title',
            'description' => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.description',
            'report'      => HandlerReport::class,
        ];
    }
);
