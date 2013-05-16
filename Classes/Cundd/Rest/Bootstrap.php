<?php
namespace Cundd\Rest;
use TYPO3\CMS\Core\Utility\GeneralUtility as GeneralUtility;
use TYPO3\CMS\Frontend\Utility\EidUtility as EidUtility;

class Bootstrap {
	/**
	 * Initializes the TYPO3 environment.
	 *
	 * @return	void
	 */
	public function init() {
		EidUtility::connectDB();
		$this->initTSFE();
	}

	/**
	 * Initialize the TSFE.
	 *
	 * @param	integer	$pageUid	 The page UID
	 * @param	boolean	$overrule
	 * @return	void
	 */
	public function initTSFE($pageUid = -1, $overrule = FALSE) {
		$typo3confVariables = $GLOBALS['TYPO3_CONF_VARS'];
		if ($pageUid == -1) {
			$pageUid = intval(GeneralUtility::_GP('pid'));
		}

		#$typo3confVariables['EXT']['extList'] = str_replace('tq_seo', '', $typo3confVariables['EXT']['extList']);
		#$typo3confVariables['EXT']['extList_FE'] = str_replace('tq_seo', '', $typo3confVariables['EXT']['extList_FE']);

		// declare
		//$temp_TSFEclassName = t3lib_div::makeInstance('tslib_fe');

		// begin
		if (!is_object($GLOBALS['TT']) || $overrule === TRUE) {
			$GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\TimeTracker();
			$GLOBALS['TT']->start();
		}

		if ((!is_object($GLOBALS['TSFE']) || $GLOBALS['TSFE'] instanceof \stdClass || $overrule === TRUE) && is_int($pageUid)) {
			// builds TSFE object
			$GLOBALS['TSFE'] = new \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController(
				$typo3confVariables,
				$pageUid,
				0, 		// Type
				0, 		// no_cache
				'', 	// cHash
				'', 	// jumpurl
				'', 	// MP,
				'' 		// RDCT
				);

			// builds rootline
			$GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance('t3lib_pageSelect');
//			$rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($pageUid);
//			$GLOBALS['TSFE']->rootLine = $rootLine;

			// init template
			$GLOBALS['TSFE']->tmpl = GeneralUtility::makeInstance('t3lib_tsparser_ext');
			$GLOBALS['TSFE']->tmpl->tt_track = 0;// Do not log time-performance information
			$GLOBALS['TSFE']->tmpl->init();

			// this generates the constants/config + hierarchy info for the template.
//			$GLOBALS['TSFE']->tmpl->runThroughTemplates(
//				$rootLine,
//				0 // start_template_uid
//				);
			$GLOBALS['TSFE']->tmpl->generateConfig();
			$GLOBALS['TSFE']->tmpl->loaded = 1;

			// get config array and other init from pagegen
//			$GLOBALS['TSFE']->getConfigArray();
//			$GLOBALS['TSFE']->linkVars = '' . $GLOBALS['TSFE']->config['config']['linkVars'];

			// builds a cObj
			$GLOBALS['TSFE']->newCObj();

			// Add the FE user
			$GLOBALS['TSFE']->fe_user = EidUtility::initFeUser();
		}
	}
}
?>