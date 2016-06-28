<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Daniel Corn
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*
 * rest
 * @author daniel
 * Date: 21.09.13
 * Time: 20:49
 */
namespace Cundd\Rest;

use TYPO3\CMS\Core\Core\Bootstrap;

class server
{
    const TYPO3_BOOTSTRAP_PATH = 'typo3/sysext/core/Classes/Core/Bootstrap.php';
    const TYPO3_AUTOLOADER_PATH = 'vendor/autoload.php';

    /**
     * @var int
     */
    private $port = 1337;

    /**
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * BuiltinServerBootstrap constructor.
     *
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        if (isset($argv[1])) {
            $this->port = $argv[1];
        }
        if (isset($argv[2])) {
            $this->host = $argv[2];
        }
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        $this->bootstrapSystem();
        return new Server($this->port, $this->host);
    }

    private function bootstrapSystem()
    {
        // Defining circumstances for CLI mode:
        define('TYPO3_cliMode', true);
        define('TYPO3_MODE', 'CLI');

        $this->requireTYPO3();
        $this->bootstrapTYPO3();
        $this->requireComposer();
    }

    private function requireTYPO3()
    {
        $pathsToCheck = array(
            './',
            __DIR__ . '/../../../',
            getenv('REST_TYPO3_BASE_PATH'),
            getenv('TYPO3_PATH_WEB'),
        );

        $pathsToCheck = array_filter($pathsToCheck);
        foreach ($pathsToCheck as $path) {
            $path = rtrim($path, '/') . '/';

            $fullPath = '';
            if (file_exists($path . 'typo3/../' . self::TYPO3_AUTOLOADER_PATH)) {
                $fullPath = $path . 'typo3/../' . self::TYPO3_AUTOLOADER_PATH;
            } elseif (file_exists($path . 'typo3/../typo3_src/' . self::TYPO3_AUTOLOADER_PATH)) {
                $fullPath = $path . 'typo3/../typo3_src/' . self::TYPO3_AUTOLOADER_PATH;
            } elseif (file_exists($path . self::TYPO3_BOOTSTRAP_PATH)) {
                $fullPath = $path . self::TYPO3_BOOTSTRAP_PATH;
            }

            if ($fullPath) {
                require_once $fullPath;
                return;
            }
        }
        throw new \Exception('Could not find TYPO3 folder. Please set the environment variable REST_TYPO3_BASE_PATH');
    }

    private function requireComposer()
    {
        if (file_exists(__DIR__ . '/vendor/react/')) {
            require_once __DIR__ . '/vendor/autoload.php';
        } elseif (class_exists('Cundd\\CunddComposer\\Autoloader')) {
            \Cundd\CunddComposer\Autoloader::register();
        }
    }

    private function bootstrapTYPO3()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->baseSetup('typo3conf/ext/rest/');

        $bootstrap
            ->loadConfigurationAndInitialize()
            ->loadTypo3LoadedExtAndExtLocalconf(true)
            ->applyAdditionalConfigurationSettings()
            ->initializeTypo3DbGlobal();
    }
}


if (php_sapi_name() != 'cli') {
    die('Access denied.');
}

if (!isset($argv)) {
    $argv = array();
}
$bootstrap = new BuiltinServerBootstrap($argv);
$bootstrap->getServer()->start();
