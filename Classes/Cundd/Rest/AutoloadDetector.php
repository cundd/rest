<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 08/09/16
 * Time: 14:55
 */

namespace Cundd\Rest;

/**
 * Class to load a matching autoloader
 */
class AutoloadDetector
{
    /**
     * Find and register a matching autoloader
     *
     * First the function will check if a "vendor" directory exists in the extensions root directory,
     * otherwise it will check if \Cundd\CunddComposer\Autoloader exists.
     */
    public function registerAutoloader()
    {
        if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
            require_once __DIR__ . '/../../../vendor/autoload.php';
        } elseif (class_exists('Cundd\\CunddComposer\\Autoloader')) {
            \Cundd\CunddComposer\Autoloader::register();
        }

        if (!class_exists('Cundd\\Rest\\Bootstrap')) {
            header('HTTP/1.0 503 Service Unavailable');
            header('Content-Type: application/json');
            echo(json_encode(
                array(
                    'error' => 'Could not find class "\\Cundd\\Rest\\Bootstrap". Maybe the Composer dependencies have not been installed.',
                    'see' => 'See https://rest.cundd.net/Installation/ for details',
                )
            ));
            exit(1);
        }
    }
}
