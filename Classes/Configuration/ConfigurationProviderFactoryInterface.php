<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

interface ConfigurationProviderFactoryInterface
{
    public function build(
        ServerRequestInterface $request,
    ): ConfigurationProviderInterface;

    public function buildFromSite(Site $site): ConfigurationProviderInterface;
}
