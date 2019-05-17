<?php
declare(strict_types=1);

namespace Cundd\Rest;

use Locale;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;
use TYPO3\CMS\Lang\LanguageService;
use function class_exists;
use function intval;
use function is_int;
use function is_string;


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
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
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
            /** @var LanguageService $GLOBALS ['LANG'] */
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
            $GLOBALS['LANG']->init($this->getRequestedLanguageCode());
        }
    }

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
    private function buildFrontendController(int $pageUid): TypoScriptFrontendController
    {
        $cHash = GeneralUtility::_GP('cHash') ?: 'cunddRestFakeHash';

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        return $objectManager->get(
            TypoScriptFrontendController::class,
            null, // previously TYPO3_CONF_VARS
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
    private function getPageUid(): int
    {
        return GeneralUtility::_GP('pid') !== null
            ? intval(GeneralUtility::_GP('pid'))
            : 0;
    }

    /**
     * @return bool
     */
    private function getFrontendControllerIsInitialized(): bool
    {
        return isset($GLOBALS['TSFE'])
            && is_object($GLOBALS['TSFE'])
            && !($GLOBALS['TSFE'] instanceof stdClass);
    }

    /**
     * Configure the given frontend controller
     *
     * @param TypoScriptFrontendController $frontendController
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
     */
    private function configureFrontendController(TypoScriptFrontendController $frontendController)
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

        $this->detectAndSetRequestedLanguage($frontendController);
        //try {
        //    $frontendController->settingLanguage();
        //} catch (\RuntimeException $exception) {
        //}
        $frontendController->settingLocale();
    }

    /**
     * Configure the system to use the requested language UID
     *
     * @param TypoScriptFrontendController $frontendController
     */
    private function detectAndSetRequestedLanguage(TypoScriptFrontendController $frontendController)
    {
        if (!isset($GLOBALS['TYPO3_REQUEST']) || !class_exists(SiteMatcher::class)) {
            $this->setRequestedLanguage($frontendController, $this->getRequestedLanguageUid($frontendController));

            return;
        }

        // support new TYPO3 v9.2 Site Handling until middleware concept is implemented
        // see https://github.com/cundd/rest/issues/59
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];

        /**
         * Try to detect the language ID from request parameters or headers. If the SiteMatcher detects a language this
         * fallback will **not** be used
         *
         * @var int|null $fallbackLanguageId
         */
        $fallbackLanguageId = (int)($request->getQueryParams()['L']
            ?? $request->getParsedBody()['L']
            ?? $this->getRequestedLanguageUid($frontendController));


        /** @var SiteRouteResult $routeResult */
        $routeResult = GeneralUtility::makeInstance(SiteMatcher::class)->matchRequest($request);


        $site = $routeResult->getSite();
        $language = $routeResult->getLanguage();

        // If TYPO3 could not determine the language for the request use the detected fallback
        if (!$language && $fallbackLanguageId !== null) {
            $language = $site->getLanguageById($fallbackLanguageId);
        }

        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('language', $language);
        $request = $request->withAttribute('routing', $routeResult);

        // Patch the original Request so that at least `site` and `routing` are defined
        $GLOBALS['TYPO3_REQUEST'] = $request
            ->withAttribute('site', $site)
            ->withAttribute('language', $language)
            ->withAttribute('routing', $routeResult);

        // Set language if defined
        if ($language && $language->getLanguageId() !== null) {
            $this->setRequestedLanguage($frontendController, $language->getLanguageId());
        } else {
            $this->setRequestedLanguage($frontendController, $fallbackLanguageId);
        }
    }

    /**
     * Detect the language UID for the requested language
     *
     * @param TypoScriptFrontendController $frontendController
     * @return int|null
     */
    private function getRequestedLanguageUid(TypoScriptFrontendController $frontendController): ?int
    {
        if (GeneralUtility::_GP('L') !== null) {
            return (int)GeneralUtility::_GP('L');
        }
        if (GeneralUtility::_GP('locale') !== null) {
            $languageId = $this->getLanguageIdForCode($frontendController, GeneralUtility::_GP('locale'));
            if ($languageId !== null) {
                return $languageId;
            }
        }

        // Test the full HTTP_ACCEPT_LANGUAGE header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languageId = $this->getLanguageIdForCode(
                $frontendController,
                (string)$_SERVER['HTTP_ACCEPT_LANGUAGE']
            );

            if ($languageId !== null) {
                return $languageId;
            }
        }

        // Retrieve and test the parsed header
        $languageCode = $this->getRequestedLanguageCode();
        if ($languageCode !== null) {
            $languageId = $this->getLanguageIdForCode($frontendController, $languageCode);
            if ($languageId !== null) {
                return $languageId;
            }
        }

        return null;
    }

    /**
     * Retrieve the TypoScript configuration for the given key path
     *
     * @param string                       $keyPath
     * @param TypoScriptFrontendController $typoScriptFrontendController
     * @return mixed
     */
    private function readConfigurationFromTyposcript(
        string $keyPath,
        TypoScriptFrontendController $typoScriptFrontendController
    ) {
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
     * Detect the requested language
     *
     * @return null|string
     */
    private function getRequestedLanguageCode(): ?string
    {
        if (class_exists('Locale') && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            /** @noinspection PhpComposerExtensionStubsInspection */
            return Locale::getPrimaryLanguage(Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']));
        }

        return null;
    }

    /**
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $languageCode
     * @return int
     */
    private function getLanguageIdForCode(TypoScriptFrontendController $frontendController, string $languageCode): ?int
    {
        $value = $this->readConfigurationFromTyposcript(
            'plugin.tx_rest.settings.languages.' . $languageCode,
            $frontendController
        );
        if (is_int($value)) {
            return $value;
        } elseif (is_string($value)) {
            return trim($value) === '' ? null : (int)$value;
        } else {
            return null;
        }
    }

    /**
     * @param TypoScriptFrontendController $frontendController
     * @param int|null                     $requestedLanguageUid
     */
    private function setRequestedLanguage(
        TypoScriptFrontendController $frontendController,
        ?int $requestedLanguageUid
    ): void {
        if (null !== $requestedLanguageUid) {
            $frontendController->config['config']['sys_language_uid'] = $requestedLanguageUid;
            // Add LinkVars and language to work with correct localized labels
            $frontendController->config['config']['linkVars'] = 'L(int)';
            $frontendController->config['config']['language'] = $this->getRequestedLanguageCode();
        }
    }
}
