<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

abstract class AbstractLanguageBootstrap implements LanguageBootstrapInterface
{
    public function prepareRequest(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ): ServerRequestInterface {
        $requestedLanguage = $request->getAttribute('language');
        if ($requestedLanguage) {
            $this->setLanguageAspect($request->getAttribute('language'));

            return $request;
        }

        /** @var Site $site */
        $site = $request->getAttribute('site');
        $defaultLanguage = $site->getDefaultLanguage();
        $this->setLanguageAspect($defaultLanguage);

        return $request->withAttribute('language', $defaultLanguage);
    }

    protected function setLanguageAspect(SiteLanguage $language): void
    {
        $languageAspect = LanguageAspectFactory::createFromSiteLanguage($language);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', $languageAspect);
    }
}
