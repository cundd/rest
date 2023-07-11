<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Configuration;

use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;

/**
 * More tests in Tests/Unit/Configuration/TypoScriptConfigurationProviderTest.php
 */
class TypoScriptConfigurationProviderTest extends AbstractConfigurationProviderCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->getContainer()->get(TypoScriptConfigurationProvider::class);
        $this->fixture->setSettings($this->settings);
    }
}
