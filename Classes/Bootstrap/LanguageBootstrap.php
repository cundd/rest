<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Bootstrap\Language\LanguageInformation;
use Cundd\Rest\Exception\InvalidLanguageException;
use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Lang\LanguageService;

class LanguageBootstrap
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
            /** @var LanguageService $GLOBALS ['LANG'] */
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
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
    ) {
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
     * Detect the language UID for the requested language
     *
     * - If `$_GET['L']` or `$_POST['L']` are defined, the value will be returned.
     * - If `$_GET['locale']` is defined the TypoScript value `plugin.tx_rest.settings.languages.{locale from GET}` will
     *      be returned if set, otherwise a `InvalidLanguageException` will be thrown.
     * - If an `Accept-Language` header is sent, the preferred language will be extracted and looked up in
     *      `plugin.tx_rest.settings.languages.{preferred language header}`. If the language is registered in
     *      TypoScript the value will be returned.
     * - If none of the above is true `NULL` will be returned
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     * @return int|null
     */
    private function getRequestedLanguageUid(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ): ?int {
        // Check $_GET['L']
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['L'])) {
            return (int)$queryParams['L'];
        }

        // Check $_POST['L']
        $parsedBody = $request->getParsedBody();
        if (isset($parsedBody['L'])) {
            return (int)$parsedBody['L'];
        }

        // Check $_GET['locale']
        if (isset($queryParams['locale'])) {
            $languageId = $this->getLanguageIdForCode(
                $frontendController,
                $queryParams['locale']
            );

            if ($languageId === null) {
                throw new InvalidLanguageException(
                    sprintf('Requested locale "%s" could not be found', $queryParams['locale'])
                );
            }

            return $languageId;
        }

        // Check the full Accept-Language header
        $languageId = $this->getLanguageIdForCode($frontendController, $request->getHeaderLine('Accept-Language'));
        if ($languageId !== null) {
            return $languageId;
        }

        // Check the primary language
        $languageCode = $this->getRequestedPrimaryLanguageCode($request);
        if ($languageCode === null) {
            return null;
        }

        $languageId = $this->getLanguageIdForCode($frontendController, $languageCode);
        if ($languageId !== null) {
            return $languageId;
        }

        return null;
    }

    /**
     * Look up the TypoScript configuration for the language UID for the given language code
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $languageCode
     * @return int
     */
    private function getLanguageIdForCode(TypoScriptFrontendController $frontendController, string $languageCode): ?int
    {
        if ('' === trim($languageCode)) {
            return null;
        }
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
     * Look up the TypoScript configuration for the language code matching the given language UID
     *
     * Reverse lookup for `getLanguageIdForCode()`
     *
     * @param TypoScriptFrontendController $frontendController
     * @param int                          $languageUid
     * @return string|null
     */
    private function getLanguageCodeForId(TypoScriptFrontendController $frontendController, int $languageUid): ?string
    {
        $languages = $this->readConfigurationFromTyposcript(
            'plugin.tx_rest.settings.languages',
            $frontendController
        );

        foreach ($languages as $code => $uid) {
            if (is_numeric($uid) && (int)$uid === $languageUid) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Retrieve the TypoScript configuration for the given key path
     *
     * @param string                       $keyPath
     * @param TypoScriptFrontendController $frontendController
     * @return mixed
     */
    private function readConfigurationFromTyposcript(
        string $keyPath,
        TypoScriptFrontendController $frontendController
    ) {
        $keyPathParts = explode('.', (string)$keyPath);
        $currentValue = $frontendController->tmpl->setup;

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
     * Detect the preferred language from the request headers
     *
     * @param ServerRequestInterface $request
     * @return null|string
     */
    private function getRequestedPrimaryLanguageCode(ServerRequestInterface $request): ?string
    {
        $headerValue = $request->getHeaderLine('Accept-Language');
        if (!$headerValue) {
            return null;
        }

        if (class_exists('Locale')) {
            /** @noinspection PhpComposerExtensionStubsInspection PhpFullyQualifiedNameUsageInspection */
            return \Locale::getPrimaryLanguage((string)\Locale::acceptFromHttp($headerValue));
        }

        if (preg_match('/^[a-z]{2}/', $headerValue, $matches)) {
            return $matches[0];
        }

        return null;
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
