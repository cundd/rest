<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 16/01/2017
 * Time: 21:05
 */
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$tempColumns = array(
    'tx_rest_apikey' => array(
        'exclude' => 1,
        'label' => 'API Key',
        'config' => array(
            'type' => 'input',
            'size' => '30',
            'eval' => 'nospace',
        )
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_rest_apikey;;;;1-1-1', '', 'after:password');
