<?php

use Cundd\CustomRest\Controller\PersonController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

(static function () {
    ExtensionUtility::configurePlugin(
        'CustomRest',
        'customRest',
        [PersonController::class => 'list,show,firstName,lastName,birthday,create,update'],
        [PersonController::class => '']
    );
})();
