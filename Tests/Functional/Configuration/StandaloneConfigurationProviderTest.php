<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Configuration;

use Cundd\Rest\Configuration\StandaloneConfigurationProvider;

/**
 * More tests in Tests/Unit/Configuration/StandaloneConfigurationProviderTest.php
 */
class StandaloneConfigurationProviderTest extends AbstractConfigurationProviderCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = new StandaloneConfigurationProvider($this->settings);
    }
}
