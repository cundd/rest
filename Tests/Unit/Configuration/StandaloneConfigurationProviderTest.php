<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\StandaloneConfigurationProvider;

class StandaloneConfigurationProviderTest extends AbstractConfigurationProviderCase
{
    public function getConfigurationProviderToTest(): StandaloneConfigurationProvider
    {
        return new StandaloneConfigurationProvider([]);
    }
}
