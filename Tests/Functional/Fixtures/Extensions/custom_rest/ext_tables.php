<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        ExtensionUtility::registerPlugin(
            'Cundd.CustomRest',
            'customRest',
            'Custom Rest - List '
        );

        ExtensionManagementUtility::addLLrefForTCAdescr(
            'tx_customrest_domain_model_person',
            'EXT:custom_rest/Resources/Private/Language/locallang_csh_tx_customrest_domain_model_person.xlf'
        );
        ExtensionManagementUtility::allowTableOnStandardPages(
            'tx_customrest_domain_model_person'
        );
        ExtensionManagementUtility::addStaticFile(
            'custom_rest',
            'Configuration/TypoScript',
            'Custom Rest Configuration'
        );
    }
);
