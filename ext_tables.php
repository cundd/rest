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


if (version_compare(TYPO3_version, '6.0.0') < 0) {
	require_once __DIR__ . '/ext/rest/legacy_core.php';
}


if (method_exists('TYPO3\CMS\Core\Utility\GeneralUtility', 'loadTCA')) {
	\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_rest_apikey;;;;1-1-1', '', 'after:password');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rest_domain_model_document', 'EXT:rest/Resources/Private/Language/locallang_csh_tx_rest_domain_model_document.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rest_domain_model_document');
$TCA['tx_rest_domain_model_document'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:tx_rest_domain_model_document',
		'label' => 'id',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'id,db,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Document.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/document.gif'
	),
);


?>
