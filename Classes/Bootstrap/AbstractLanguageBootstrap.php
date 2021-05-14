<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\Exception\InvalidLanguageException;
use Locale;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use function class_exists;

abstract class AbstractLanguageBootstrap implements LanguageBootstrapInterface
{
    /**
     * Enhance the Request with the major requested language
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     * @return ServerRequestInterface
     */
    public function prepareRequest(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ): ServerRequestInterface {
        $requestedLanguageUid = $this->getRequestedLanguageUid($frontendController, $request);

        // If a language is requested explicitly look if it is available in the Site
        if ($requestedLanguageUid !== null) {
            /** @var Site $site */
            $site = $request->getAttribute('site');
            $language = $site->getLanguageById($requestedLanguageUid);

            $this->setLanguageAspect($language);

            return $request
                ->withAttribute('language', $language);
        }

        // TODO: Loop through the languages and find the matching one (v5.2)
        // $requestedPrimaryLanguageCode = $this->getRequestedPrimaryLanguageCode($request);
        // if ($requestedPrimaryLanguageCode) {
        //     /** @var Site $site */
        //     $site = $request->getAttribute('site');
        //     foreach ($site->getAllLanguages() as $language) {
        //         if ($language->getLocale() === $requestedPrimaryLanguageCode) {
        //             return $request->withAttribute('language', $language);
        //         }
        //     }
        // }

        $this->setLanguageAspect($request->getAttribute('language'));

        return $request;
    }

    /**
     * Detect the language UID for the requested language
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
    protected function getRequestedLanguageUid(
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
    protected function getLanguageIdForCode(
        TypoScriptFrontendController $frontendController,
        string $languageCode
    ): ?int {
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
     * Reverse lookup for `getLanguageIdForCode()`
     *
     * @param TypoScriptFrontendController $frontendController
     * @param int                          $languageUid
     * @return string|null
     */
    protected function getLanguageCodeForId(TypoScriptFrontendController $frontendController, int $languageUid): ?string
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
    protected function readConfigurationFromTyposcript(
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
    protected function getRequestedPrimaryLanguageCode(ServerRequestInterface $request): ?string
    {
        $headerValue = $request->getHeaderLine('Accept-Language');
        if (!$headerValue) {
            return null;
        }

        if (class_exists('Locale')) {
            return Locale::getPrimaryLanguage((string)Locale::acceptFromHttp($headerValue));
        }

        if (preg_match('/^[a-z]{2}/', $headerValue, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * @param SiteLanguage $language
     */
    protected function setLanguageAspect(SiteLanguage $language): void
    {
        $languageAspect = LanguageAspectFactory::createFromSiteLanguage($language);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', $languageAspect);
    }
}
