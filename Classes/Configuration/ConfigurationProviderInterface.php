<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 09/05/16
 * Time: 20:56
 */
namespace Cundd\Rest\Configuration;

/**
 * Interface for configuration providers
 */
interface ConfigurationProviderInterface
{
    /**
     * Returns the setting with the given key
     *
     * @param string $keyPath
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getSetting($keyPath, $defaultValue = null);

    /**
     * Returns the settings read from the TypoScript
     *
     * @return array
     */
    public function getSettings();
}
