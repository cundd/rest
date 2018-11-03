<?php

namespace Cundd\Rest\Tests\Functional\Configuration;

use Cundd\Rest\Configuration\StandaloneConfigurationProvider;

/**
 * More tests in Tests/Unit/Configuration/StandaloneConfigurationProviderTest.php
 */
class StandaloneConfigurationProviderTest extends AbstractConfigurationProviderCase
{
    public function setUp()
    {
        parent::setUp();
        $this->fixture = new StandaloneConfigurationProvider($this->settings);
    }
}
