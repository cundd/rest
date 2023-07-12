<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\V11;

use Cundd\Rest\Bootstrap\AbstractCoreBootstrap;
use Cundd\Rest\Utility\SiteLanguageUtility;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use function is_array;
use function is_callable;

class V11CoreBootstrap extends AbstractCoreBootstrap
{
    public function initialize(ServerRequestInterface $request): ServerRequestInterface
    {
        $frontendController = $this->buildFrontendController($this->getPageUid($request), $request);

        // Register the frontend controller as the global TSFE
        // Currently required to populate the TypoScriptConfigurationProvider`s configuration
        // TODO: Remove this requirement
        $GLOBALS['TSFE'] = $frontendController;

        return $this->configureFrontendController($frontendController, $request)
            ->withAttribute('frontend.controller', $frontendController);
    }

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

    protected function configureFrontendController(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ): ServerRequestInterface {
        if (!is_array($frontendController->page)) {
            $frontendController->page = [];
        }

        // Build an instance of ContentObjectRenderer
        $frontendController->newCObj();

        $frontendController->determineId($request);
        if (is_callable([$frontendController, 'getConfigArray'])) {
            $frontendController->getConfigArray();
        }

        return $request;
    }
}
