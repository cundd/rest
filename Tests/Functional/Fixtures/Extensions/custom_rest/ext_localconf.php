<?php

use Cundd\CustomRest\Controller\PersonController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function () {
    ExtensionUtility::configurePlugin(
        'CustomRest',
        'customRest',
        [PersonController::class => 'list,show,firstName,lastName,birthday,create,update'],
        [PersonController::class => '']
    );
})();
