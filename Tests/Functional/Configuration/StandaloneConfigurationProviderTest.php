<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Configuration;

use Cundd\Rest\Configuration\StandaloneConfigurationProvider;

/**
 * @see \Cundd\Rest\Tests\Unit\Configuration\StandaloneConfigurationProviderTest
 */
class StandaloneConfigurationProviderTest extends AbstractConfigurationProviderCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = new StandaloneConfigurationProvider($this->settings);
    }
}
