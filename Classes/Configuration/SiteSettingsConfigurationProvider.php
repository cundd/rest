<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class SiteSettingsConfigurationProvider extends AbstractConfigurationProvider
{
    protected ConfigurationManager $configurationManager;

    public function __construct(private readonly SiteSettings $siteSettings)
    {
    }

    public function getSettings(): array
    {
        $allSettings = $this->siteSettings->get('rest.settings');
        $restNamespace = $this->siteSettings->get('rest');
        if (!is_array($restNamespace)) {
            throw new InvalidConfigurationException(
                'Could not find Site Settings for REST'
            );
        }

        $settings = $restNamespace['settings'] ?? [];
        if (empty($settings)) {
            throw new InvalidConfigurationException(
                'Site Settings for REST are empty'
            );
        }

        return $settings;
    }
}
