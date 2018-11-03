<?php

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;

class TypoScriptConfigurationProviderTest extends AbstractConfigurationProviderCase
{
    function getConfigurationProviderToTest()
    {
        return new TypoScriptConfigurationProvider();
    }
}
