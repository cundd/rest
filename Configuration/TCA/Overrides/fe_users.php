<?php

use Cundd\Rest\Authentication\UserProvider\FeUserProvider;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$tempColumns = [
    FeUserProvider::PASSWORD_COLUMN_NAME => [
        'exclude' => 1,
        'label'   => 'API Key',
        'config'  => [
            'type'           => 'password',
            'passwordPolicy' => 'default',
            'fieldControl'   => [
                'passwordGenerator' => [
                    'renderType' => 'passwordGenerator',
                    'options'    => [
                        'passwordPolicy' => 'default',
                    ],
                ],
            ],
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    $tempColumns
);
ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    FeUserProvider::PASSWORD_COLUMN_NAME . ';;;;1-1-1',
    '',
    'after:password'
);
