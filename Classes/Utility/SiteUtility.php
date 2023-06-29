<?php

declare(strict_types=1);

namespace Cundd\Rest\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

class SiteUtility
{
    /**
     * Fetch the Site's from the Request
     *
     * @param ServerRequestInterface $request
     * @return Site|null
     */
    public static function detectSite(ServerRequestInterface $request): ?Site
    {
        return $request->getAttribute('site');
    }

    /**
     * Return the URI prefix for the current Site
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public static function detectSitePrefix(ServerRequestInterface $request): string
    {
        $site = self::detectSite($request);
        if ($site) {
            return (string)$site->getBase()->getPath();
        } else {
            return '/';
        }
    }
}
