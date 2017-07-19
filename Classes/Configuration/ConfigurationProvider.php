<?php

namespace Cundd\Rest\Configuration;

/**
 * Alias for the concrete implementation of ConfigurationProviderInterface
 *
 * Workaround for the ObjectManager to find the implementation without the full TypoScript loaded
 */
class ConfigurationProvider extends TypoScriptConfigurationProvider
{
}
