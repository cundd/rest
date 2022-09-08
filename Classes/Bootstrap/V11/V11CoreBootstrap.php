<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\V11;

use Cundd\Rest\Bootstrap\V10\V10CoreBootstrap;
use Cundd\Rest\Utility\SiteLanguageUtility;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class V11CoreBootstrap extends V10CoreBootstrap
{
    /**
     * Build the TSFE object
     *
     * @param int                    $pageUid
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     */
    protected function buildFrontendController(
        int $pageUid,
        ServerRequestInterface $request
    ): TypoScriptFrontendController {
        $context = GeneralUtility::makeInstance(Context::class);
        $siteLanguage = SiteLanguageUtility::detectSiteLanguage($request);
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        $pageArguments = $request->getAttribute('routing', null);
        if (!$pageArguments instanceof PageArguments) {
            $pageArguments = new PageArguments($pageUid, '0', []);
        }
        $frontendUser = $request->getAttribute('frontend.user');
        if (!$frontendUser instanceof FrontendUserAuthentication) {
            throw new RuntimeException(
                'The PSR-7 Request attribute "frontend.user" needs to be available as FrontendUserAuthentication object (as created by the FrontendUserAuthenticator middleware).',
                1590740612
            );
        }

        return GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $context,
            $site,
            $siteLanguage ?? $site->getDefaultLanguage(),
            $pageArguments,
            $frontendUser
        );
    }
}
