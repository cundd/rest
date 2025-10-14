<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Cundd\Rest\ObjectManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class to bootstrap TYPO3 frontend controller
 */
abstract class AbstractCoreBootstrap implements CoreBootstrapInterface
{
    public function __construct(protected ObjectManagerInterface $objectManager)
    {
    }

    /**
     * Build the TSFE object
     */
    abstract protected function buildFrontendController(
        int $pageUid,
        ServerRequestInterface $request,
    ): TypoScriptFrontendController;

    /**
     * Configure the given frontend controller
     */
    abstract protected function configureFrontendController(
        TypoScriptFrontendController $frontendController,
        ServerRequestInterface $request,
    ): ServerRequestInterface;

    protected function getPageUid(ServerRequestInterface $request): int
    {
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['pid'])) {
            return (int) $queryParams['pid'];
        }
        /** @var Site|null $site */
        $site = $request->getAttribute('site');

        return $site ? $site->getRootPageId() : 0;
    }
}
