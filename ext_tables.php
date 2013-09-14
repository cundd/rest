<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
	'tx_rest_apikey' => array (
		'exclude' => 1,
		'label' => 'API Key',
		'config' => array (
			'type' => 'input',
			'size' => '30',
			'eval' => 'nospace',
		)
	),
);


\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_rest_apikey;;;;1-1-1', '', 'after:password');
?>