<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

/**
 * Workaround to enable loading the correct implementation for `ConfigurationProviderInterface`
 */
class ConfigurationProvider extends TypoScriptConfigurationProvider
{
}
