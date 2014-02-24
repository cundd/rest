<?php
require_once __DIR__ . '/legacy_core.php';

class_alias('t3lib_tsparser_ext', 		'TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService');
class_alias('tslib_eidtools', 			'TYPO3\\CMS\\Frontend\\Utility\\EidUtility');
class_alias('tslib_fe', 				'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController');

class_alias('Tx_Extbase_Object_ObjectManagerInterface',						'TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface');
class_alias('Tx_Extbase_Object_ObjectManager',								'TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
class_alias('Tx_Extbase_Configuration_ConfigurationManagerInterface', 		'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
class_alias('Tx_Extbase_Configuration_ConfigurationManager', 				'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
class_alias('Tx_Extbase_Property_PropertyMapper', 							'TYPO3\\CMS\\Extbase\\Property\\PropertyMapper');

class_alias('Tx_Extbase_Reflection_Service', 'TYPO3\\CMS\\Extbase\\Reflection\\ReflectionService');

class_alias('Tx_Extbase_Persistence_Repository', 'TYPO3\\CMS\\Extbase\\Persistence\\Repository');
class_alias('Tx_Extbase_Persistence_RepositoryInterface', 'TYPO3\\CMS\\Extbase\\Persistence\\RepositoryInterface');
class_alias('Tx_Extbase_Persistence_Typo3QuerySettings', 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

class_alias('Tx_Extbase_Persistence_QuerySettingsInterface', 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface');
class_alias('Tx_Extbase_DomainObject_DomainObjectInterface', 'TYPO3\\CMS\\Extbase\\DomainObject\\DomainObjectInterface');



class_alias('Tx_Extbase_Persistence_PersistenceManagerInterface', 'TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');



class_alias('Tx_Extbase_Persistence_Manager', 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');


class_alias('Tx_Extbase_Persistence_PersistenceManagerInterface', 'TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');


class_alias('Tx_Extbase_Persistence_ManagerInterface', 'TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');


class_alias('Tx_Rest_Domain_Model_Document', 'Cundd\\Rest\\Domain\\Model\\Document');



/**
 * Class TYPO3_CMS_Core_Log_LogManager
 */
class TYPO3_CMS_Core_Log_LogManager {
	/**
	 * @param $className
	 * @return $this
	 */
	public function getLogger($className) {
		return $this;
	}

	/**
	 * Adds a log record.
	 *
	 * @param integer $level Log level.
	 * @param string $message Log message.
	 * @param array $data Additional data to log
	 * @return mixed
	 */
	public function log($level, $message, array $data = array()) {
		if (TYPO3_DLOG) t3lib_div::devLog($message, 'rest', $level, $data);
	}
}
class_alias('TYPO3_CMS_Core_Log_LogManager', 'TYPO3\\CMS\\Core\\Log\\LogManager');


/**
 * Class LogLevel
 */
class TYPO3_CMS_Core_Log_LogLevel {
	const EMERGENCY = 0;
	const ALERT = 1;
	const CRITICAL = 2;
	const ERROR = 3;
	const WARNING = 4;
	const NOTICE = 5;
	const INFO = 6;
	const DEBUG = 7;
}
class_alias('TYPO3_CMS_Core_Log_LogLevel', 'TYPO3\\CMS\\Core\\Log\\LogLevel');
