<?php
defined('TYPO3') or die();

call_user_func(function()
{
   $extensionKey = 'rest';

    if (version_compare(TYPO3_version, '10.4.0') >= 0) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript/Page/TYPO3-10',
            'Virtual Object - Page'
        );
    } elseif (version_compare(TYPO3_version, '9.5.0') >= 0) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript/Page/TYPO3-9',
            'Virtual Object - Page'
        );
    } else {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extensionKey,
            'Configuration/TypoScript/Page/TYPO3-8',
            'Virtual Object - Page'
        );
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript/Content',
        'Virtual Object - Content'
    );

});