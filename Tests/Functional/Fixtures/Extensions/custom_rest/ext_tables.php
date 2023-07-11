<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function () {
    ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_customrest_domain_model_person',
        'EXT:custom_rest/Resources/Private/Language/locallang_csh_tx_customrest_domain_model_person.xlf'
    );
    ExtensionManagementUtility::allowTableOnStandardPages(
        'tx_customrest_domain_model_person'
    );
})();
