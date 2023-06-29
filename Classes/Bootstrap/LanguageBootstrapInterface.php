<?php

declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

interface LanguageBootstrapInterface
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
    ): ServerRequestInterface;
}
