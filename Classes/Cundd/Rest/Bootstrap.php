<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest;

use TYPO3\CMS\Core\Utility\GeneralUtility as GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Utility\EidUtility as EidUtility;

class Bootstrap {
    /**
     * Initializes the TYPO3 environment.
     *
     * @return    void
     */
    public function init() {
        if (version_compare(TYPO3_version, '6.0.0') < 0) {
            require_once __DIR__ . '/../../../legacy.php';
        }

        if (method_exists('TYPO3\CMS\Frontend\Utility\EidUtility', 'connectDB')) {
            EidUtility::connectDB();
        }
        $this->initTSFE();
    }

    /**
     * Initialize the TSFE.
     *
     * @param    integer $pageUid The page UID
     * @param    boolean $overrule
     * @return    void
     */
    public function initTSFE($pageUid = -1, $overrule = FALSE) {
        $rootLine = NULL;
        $typo3confVariables = $GLOBALS['TYPO3_CONF_VARS'];
        if ($pageUid == -1 && GeneralUtility::_GP('pid') !== NULL) {
            $pageUid = intval(GeneralUtility::_GP('pid'));
        }
        if ($pageUid === -1) {
            $pageUid = 0;
        }

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
                0,  // Type
                0,  // no_cache
                '', // cHash
                '', // jumpurl
                '', // MP,
                ''  // RDCT
            );

            $GLOBALS['TSFE']->initTemplate();

            // builds a cObj
            if (is_array($GLOBALS['TSFE']->page) === FALSE) {
                $GLOBALS['TSFE']->page = array();
            }
            $GLOBALS['TSFE']->newCObj();

            // Add the FE user
            $GLOBALS['TSFE']->fe_user = EidUtility::initFeUser();

            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->getConfigArray();
        }
    }
}
