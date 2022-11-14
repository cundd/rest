<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(static function () {
    ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_customrest_domain_model_person',
        'EXT:custom_rest/Resources/Private/Language/locallang_csh_tx_customrest_domain_model_person.xlf'
    );
    ExtensionManagementUtility::allowTableOnStandardPages(
        'tx_customrest_domain_model_person'
    );
})();
