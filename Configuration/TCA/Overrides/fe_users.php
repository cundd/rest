<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

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

ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    $tempColumns
);
ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'tx_rest_apikey;;;;1-1-1',
    '',
    'after:password'
);
