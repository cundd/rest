<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\V10;

use Cundd\Rest\Bootstrap\AbstractCoreBootstrap;
use Cundd\Rest\Utility\SiteLanguageUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

use function is_array;

class V10CoreBootstrap extends AbstractCoreBootstrap
{
    /**
     * Initializes the TYPO3 environment
     *
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     */
    public function initialize(ServerRequestInterface $request): TypoScriptFrontendController
    {
        $frontendController = $this->buildFrontendController($this->getPageUid($request), $request);

        // Register the frontend controller as the global TSFE
        // Currently required to populate the TypoScriptConfigurationProvider`s configuration
        // TODO: Remove this requirement
        $GLOBALS['TSFE'] = $frontendController;

        $this->configureFrontendController($frontendController, $request);

        return $frontendController;
    }

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
        $site = $request->getAttribute('site');
        $siteLanguage = SiteLanguageUtility::detectSiteLanguage($request);

        /** @var TypoScriptFrontendController $frontendController */
        $frontendController = $this->objectManager->get(
            TypoScriptFrontendController::class,
            $context,
            $site,
            $siteLanguage,
            $pageUid
        );

        $frontendController->fe_user = $request->getAttribute('frontend.user');

        return $frontendController;
    }

    /**
     * Configure the given frontend controller
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface       $request
     * @throws ServiceUnavailableException
     */
    protected function configureFrontendController(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request
    ) {
        if (!is_array($frontendController->page)) {
            $frontendController->page = [];
        }

        // Build an instance of ContentObjectRenderer
        $frontendController->newCObj();

        $frontendController->determineId($request);
        $frontendController->getConfigArray();
    }
}
