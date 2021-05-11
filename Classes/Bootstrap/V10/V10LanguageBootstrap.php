<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\V10;

use Cundd\Rest\Bootstrap\AbstractLanguageBootstrap;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class V10LanguageBootstrap extends AbstractLanguageBootstrap
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
        $newRequest = parent::prepareRequest($frontendController, $request);

        $languageAspect = LanguageAspectFactory::createFromSiteLanguage($request->getAttribute('language'));
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', $languageAspect);

        return $newRequest;
    }
}
