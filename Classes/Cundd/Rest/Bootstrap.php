<?php
/*
 *  Copyright notice
 *
 *  (c) 2016 Daniel Corn <info@cundd.net>, cundd
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
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility as EidUtility;

class Bootstrap
{
    /**
     * Initializes the TYPO3 environment.
     *
     * @return    void
     */
    public function init()
    {
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
    public function initTSFE($pageUid = -1, $overrule = false)
    {
        $rootLine = null;
        $typo3confVariables = $GLOBALS['TYPO3_CONF_VARS'];
        if ($pageUid == -1 && GeneralUtility::_GP('pid') !== null) {
            $pageUid = intval(GeneralUtility::_GP('pid'));
        }
        if ($pageUid === -1) {
            $pageUid = 0;
        }

        // begin
        if (!is_object($GLOBALS['TT']) || $overrule === true) {
            $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\TimeTracker();
            $GLOBALS['TT']->start();
        }

        if ((!is_object($GLOBALS['TSFE']) || $GLOBALS['TSFE'] instanceof \stdClass || $overrule === true) && is_int($pageUid)) {
            // builds TSFE object
            $GLOBALS['TSFE'] = $typoScriptFrontendController = new \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController(
                $typo3confVariables,
                $pageUid,
                0,  // Type
                0,  // no_cache
                '', // cHash
                '', // jumpurl
                '', // MP,
                ''  // RDCT
            );

            $typoScriptFrontendController->initTemplate();

            // builds a cObj
            if (is_array($typoScriptFrontendController->page) === false) {
                $typoScriptFrontendController->page = array();
            }

            $typoScriptFrontendController->newCObj();

            // Add the FE user
            $typoScriptFrontendController->fe_user = EidUtility::initFeUser();

            $typoScriptFrontendController->determineId();
            $typoScriptFrontendController->getConfigArray();

            $this->setRequestedLanguage($typoScriptFrontendController);
            $typoScriptFrontendController->settingLanguage();
            $typoScriptFrontendController->settingLocale();
        }
    }


    private function getRequestedLanguageCode()
    {
        if (class_exists('Locale')) {
            return \Locale::getPrimaryLanguage(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']));
        }

        return null;
    }

    /**
     * @return int|null
     */
    private function getRequestedLanguageUid()
    {
        // Test the full HTTP_ACCEPT_LANGUAGE header
        $typoscriptValue = $this->readConfigurationFromTyposcript(
            'plugin.tx_rest.settings.languages.' . $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            $GLOBALS['TSFE']
        );

        if ($typoscriptValue !== null) {
            return intval($typoscriptValue);
        }

        // Retrieve and test the parsed header
        $languageCode = $this->getRequestedLanguageCode();
        if ($languageCode === null) {
            return null;
        }
        $typoscriptValue = $this->readConfigurationFromTyposcript(
            'plugin.tx_rest.settings.languages.' . $languageCode,
            $GLOBALS['TSFE']
        );

        if ($typoscriptValue === null) {
            return null;
        }

        return intval($typoscriptValue);
    }

    /**
     * @param string                       $keyPath
     * @param TypoScriptFrontendController $typoScriptFrontendController
     * @return mixed
     */
    private function readConfigurationFromTyposcript($keyPath, $typoScriptFrontendController)
    {
        $keyPathParts = explode('.', $keyPath);
        $currentValue = $typoScriptFrontendController->tmpl->setup;

        foreach ($keyPathParts as $currentKey) {
            if (isset($currentValue[$currentKey . '.'])) {
                $currentValue = $currentValue[$currentKey . '.'];
            } elseif (isset($currentValue[$currentKey])) {
                $currentValue = $currentValue[$currentKey];
            } else {
                return null;
            }
        }

        return $currentValue;
    }

    /**
     * @param TypoScriptFrontendController $typoScriptFrontendController
     */
    private function setRequestedLanguage($typoScriptFrontendController)
    {
        // Set language if defined
        if (GeneralUtility::_GP('L') !== null) {
            $typoScriptFrontendController->config['config']['sys_language_uid'] = intval(GeneralUtility::_GP('L'));
        } else {
            $requestedLanguageUid = $this->getRequestedLanguageUid();

            if (!is_null($requestedLanguageUid)) {
                $typoScriptFrontendController->config['config']['sys_language_uid'] = $requestedLanguageUid;
            }
        }
    }
}
