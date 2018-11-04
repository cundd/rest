<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$tempColumns = [
    'tx_rest_apikey' => [
        'exclude' => 1,
        'label'   => 'API Key',
        'config'  => [
            'type' => 'input',
            'size' => '30',
            'eval' => 'nospace',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    $tempColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'tx_rest_apikey;;;;1-1-1',
    '',
    'after:password'
);
