<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

interface CoreBootstrapInterface
{
    /**
     * Initializes the TYPO3 environment
     *
     * @param ServerRequestInterface $request
     * @return TypoScriptFrontendController
     */
    public function initialize(ServerRequestInterface $request): TypoScriptFrontendController;
}
