<?php

declare(strict_types=1);

namespace Cundd\Rest\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class SiteLanguageUtility
{
    /**
     * Fetch the Site's Language from the Request
     */
    public static function detectSiteLanguage(
        ServerRequestInterface $request,
    ): ?SiteLanguage {
        return $request->getAttribute('language');
    }

    /**
     * Return the language URI prefix for the current Site Language
     */
    public static function detectSiteLanguagePrefix(
        ServerRequestInterface $request,
    ): string {
        $siteLanguage = self::detectSiteLanguage($request);
        if ($siteLanguage) {
            return $siteLanguage->getBase()->getPath();
        } else {
            return '/';
        }
    }
}
