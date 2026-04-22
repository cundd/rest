<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use UnexpectedValueException;

class ConfigurationProviderFactory implements ConfigurationProviderFactoryInterface
{
    public function build(
        ServerRequestInterface $request,
    ): ConfigurationProviderInterface {
        /** @var NullSite|Site|null $site */
        $site = $request->getAttribute('site');
        if (null === $site) {
            throw new UnexpectedValueException('Could not determine Site');
        }
        if ($site instanceof NullSite) {
            throw new UnexpectedValueException('Can not fetch settings of `NullSite`');
        }

        return $this->buildFromSite($site);
    }

    public function buildFromSite(Site $site): ConfigurationProviderInterface
    {
        return new SiteSettingsConfigurationProvider($site->getSettings());
    }
}
