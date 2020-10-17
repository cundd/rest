<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\V9;

use Cundd\Rest\Bootstrap\AbstractLanguageBootstrap;
use Cundd\Rest\Bootstrap\Language\LanguageInformation;
use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use function class_exists;

class V9LanguageBootstrap extends AbstractLanguageBootstrap
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * LanguageBootstrap constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Initialize language object
     *
     * @param ServerRequestInterface $request
     */
    public function initializeLanguageObject(ServerRequestInterface $request)
    {
        if (!isset($GLOBALS['LANG']) || !is_object($GLOBALS['LANG'])) {
            if (class_exists(LanguageService::class)) {
                $languageServiceClass = LanguageService::class;
            } else {
                // TYPO3 v8
                $languageServiceClass = \TYPO3\CMS\Lang\LanguageService::class;
            }
            /** @var LanguageService $languageService */
            $languageService = GeneralUtility::makeInstance($languageServiceClass);
            $GLOBALS['LANG'] = $languageService;
            $GLOBALS['LANG']->init($this->getRequestedPrimaryLanguageCode($request));
        }
    }

    /**
     * Initialize the language settings
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     * @return TypoScriptFrontendController
     */
    public function initializeFrontendController(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ): TypoScriptFrontendController {
        $requestedLanguage = $this->detectRequestedLanguage($frontendController, $request);

        $this->setRequestedLanguage($frontendController, $requestedLanguage);

        return $frontendController;
    }

    /**
     * Detect the requested language
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     * @return LanguageInformation
     */
    private function detectRequestedLanguage(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ): ?LanguageInformation {
        $requestedLanguageUid = $this->getRequestedLanguageUid($frontendController, $request);

        // TYPO3 v8
        if (!class_exists(SiteMatcher::class)) {
            if ($requestedLanguageUid) {
                return new LanguageInformation(
                    $requestedLanguageUid,
                    $this->getLanguageCodeForId($frontendController, $requestedLanguageUid)
                );
            } else {
                return null;
            }
        }

        // support new TYPO3 v9.2 Site Handling until middleware concept is implemented
        // see https://github.com/cundd/rest/issues/59

        /** @var SiteRouteResult $routeResult */
        $routeResult = $this->objectManager->get(SiteMatcher::class)->matchRequest($request);
        $site = $routeResult->getSite();

        // If a language is requested explicitly look if it is available in the Site
        if ($requestedLanguageUid) {
            $language = $site->getLanguageById($requestedLanguageUid);
        } else {
            $language = $routeResult->getLanguage();
        }

        // Patch the original Request so that at least `site` and `routing` are defined
        $patchedRequest = $request
            ->withAttribute('site', $site)
            ->withAttribute('language', $language)
            ->withAttribute('routing', $routeResult);
        $GLOBALS['TYPO3_REQUEST'] = $patchedRequest;

        // Set language if defined
        if ($language && $language->getLanguageId() !== null) {
            return LanguageInformation::fromSiteLanguage($language);
        } elseif ($requestedLanguageUid) {
            return new LanguageInformation(
                $requestedLanguageUid,
                $this->getLanguageCodeForId($frontendController, $requestedLanguageUid)
            );
        } else {
            return null;
        }
    }

    /**
     * @param TypoScriptFrontendController $frontendController
     * @param LanguageInformation|null     $languageInformation
     */
    private function setRequestedLanguage(
        TypoScriptFrontendController $frontendController,
        ?LanguageInformation $languageInformation
    ): void {
        if (null !== $languageInformation) {
            $frontendController->config['config']['sys_language_uid'] = $languageInformation->getUid();
            // Add LinkVars and language to work with correct localized labels
            $frontendController->config['config']['linkVars'] = 'L(int)';
            $frontendController->config['config']['language'] = $languageInformation->getCode();
        }

        // Invoke the internal method to initialize the language system
        if (is_callable([$frontendController, 'settingLanguage'])) {
            $frontendController->settingLanguage();
        }
        $frontendController->settingLocale();
    }
}
