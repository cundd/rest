<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\V10;

use Cundd\Rest\Bootstrap\AbstractLanguageBootstrap;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
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
        $requestedLanguageUid = $this->getRequestedLanguageUid($frontendController, $request);

        // If a language is requested explicitly look if it is available in the Site
        if ($requestedLanguageUid) {
            /** @var Site $site */
            $site = $request->getAttribute('site');
            $language = $site->getLanguageById($requestedLanguageUid);

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

        return $request;
    }
}
