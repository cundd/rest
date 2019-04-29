<?php

namespace Cundd\Rest;

use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;
use TYPO3\CMS\Lang\LanguageService;


/**
 * Class to bootstrap TYPO3 frontend controller
 */
class Bootstrap
{
    /**
     * Initializes the TYPO3 environment
     *
     * @param TypoScriptFrontendController|null $frontendController
     * @return TypoScriptFrontendController
     */
    public function init(TypoScriptFrontendController $frontendController = null)
    {
        $this->initializeTimeTracker();
        $this->initializeLanguageObject();

        $frontendController = $frontendController ?: $this->buildFrontendController($this->getPageUid());

        if ($this->getFrontendControllerIsInitialized()) {
            return $GLOBALS['TSFE'];
        }

        // Register the frontend controller as the global TSFE
        $GLOBALS['TSFE'] = $frontendController;
        $this->configureFrontendController($frontendController);

        return $frontendController;
    }

    /**
     * Initialize language object
     */
    public function initializeLanguageObject()
    {
        if (!isset($GLOBALS['LANG']) || !is_object($GLOBALS['LANG'])) {
            /** @var \TYPO3\CMS\Lang\LanguageService $GLOBALS ['LANG'] */
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
            $GLOBALS['LANG']->init($this->getRequestedLanguageCode());
        }
    }

    /**
     *
     */
    private function initializeTimeTracker()
    {
        if (!isset($GLOBALS['TT']) || !is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new TimeTracker();
            $GLOBALS['TT']->start();
        }
    }

    /**
     * Build the TSFE object
     *
     * @param int $pageUid
     * @return TypoScriptFrontendController
     */
    private function buildFrontendController($pageUid)
    {
        $cHash = GeneralUtility::_GP('cHash') ?: 'cunddRestFakeHash';

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        return $objectManager->get(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'], // can be removed in TYPO3 v8
            $pageUid,
            0,  // Type
            0,  // no_cache
            $cHash, // cHash
            null, // previously jumpurl
            '', // MP,
            ''  // RDCT
        );
    }

    /**
     * @return int
     */
    private function getPageUid()
    {
        $pageUid = GeneralUtility::_GP('pid') !== null
            ? intval(GeneralUtility::_GP('pid'))
            : 0;

        return $pageUid;
    }

    /**
     * @return bool
     */
    private function getFrontendControllerIsInitialized()
    {
        return isset($GLOBALS['TSFE'])
            && is_object($GLOBALS['TSFE'])
            && !($GLOBALS['TSFE'] instanceof \stdClass);
    }

    /**
     * Configure the given frontend controller
     *
     * @param TypoScriptFrontendController $frontendController
     */
    private function configureFrontendController($frontendController)
    {
        $frontendController->initTemplate();

        if (!is_array($frontendController->page)) {
            $frontendController->page = [];
        }

        // Build an instance of ContentObjectRenderer
        $frontendController->newCObj();

        // Add the FE user
        $frontendController->fe_user = EidUtility::initFeUser();

        $frontendController->determineId();
        $frontendController->getConfigArray();

        $this->setRequestedLanguage($frontendController);
        $frontendController->settingLanguage();
        $frontendController->settingLocale();
    }

    /**
     * Configure the system to use the requested language UID
     *
     * @param TypoScriptFrontendController $frontendController
     */
    private function setRequestedLanguage(TypoScriptFrontendController $frontendController)
    {
        // support new Typo3 v9.2 Site Handling until middleware concept is implemented
        // see https://github.com/cundd/rest/issues/59
        if (isset($GLOBALS['TYPO3_REQUEST']) && class_exists(SiteMatcher::class)) {
            $request = $GLOBALS['TYPO3_REQUEST'];

            /** @var SiteRouteResult $routeResult */
            $routeResult = GeneralUtility::makeInstance(SiteMatcher::class)->matchRequest($request);

            $language = $routeResult->getLanguage();

            $request = $request->withAttribute('site', $routeResult->getSite());
            $request = $request->withAttribute('language', $language);
            $request = $request->withAttribute('routing', $routeResult);

            $GLOBALS['TYPO3_REQUEST'] = $request;

            // Set language if defined
            $requestedLanguageUid = ($language && $language->getLanguageId() !== null)
                ? $language->getLanguageId()
                : $this->getRequestedLanguageUid($frontendController);
        } else {
            $requestedLanguageUid = GeneralUtility::_GP('L') !== null
                ? intval(GeneralUtility::_GP('L'))
                : $this->getRequestedLanguageUid($frontendController);
        }

        if (null !== $requestedLanguageUid) {
            $frontendController->config['config']['sys_language_uid'] = $requestedLanguageUid;
        }
    }

    /**
     * Detects the language UID for the requested language
     *
     * @param TypoScriptFrontendController $frontendController
     * @return int|null
     */
    private function getRequestedLanguageUid(TypoScriptFrontendController $frontendController)
    {
        // Test the full HTTP_ACCEPT_LANGUAGE header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $typoscriptValue = $this->readConfigurationFromTyposcript(
                'plugin.tx_rest.settings.languages.' . $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                $frontendController
            );

            if ($typoscriptValue !== null) {
                return intval($typoscriptValue);
            }
        }

        // Retrieve and test the parsed header
        $languageCode = $this->getRequestedLanguageCode();
        if ($languageCode === null) {
            return null;
        }
        $typoscriptValue = $this->readConfigurationFromTyposcript(
            'plugin.tx_rest.settings.languages.' . $languageCode,
            $frontendController
        );

        if ($typoscriptValue === null) {
            return null;
        }

        return intval($typoscriptValue);
    }

    /**
     * Retrieves the TypoScript configuration for the given key path
     *
     * @param string                       $keyPath
     * @param TypoScriptFrontendController $typoScriptFrontendController
     * @return mixed
     */
    private function readConfigurationFromTyposcript($keyPath, $typoScriptFrontendController)
    {
        $keyPathParts = explode('.', (string)$keyPath);
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
     * Detects the requested language
     *
     * @return null|string
     */
    private function getRequestedLanguageCode()
    {
        if (class_exists('Locale') && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            return \Locale::getPrimaryLanguage(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']));
        }

        return null;
    }
}
