<?php
/**
 * Functional Test bootstrapping
 */

namespace Cundd\Rest\Tests\Functional;

/**
 * Bootstrap for functional tests
 */
class Bootstrap
{
    /**
     * Bootstrap the TYPO3 system
     */
    public function bootstrapSystem()
    {
        $this->setupComposer();
        $this->setupTYPO3();
        $this->setupAbstractCase();
    }

    /**
     * Setup the Composer autoloading
     */
    private function setupComposer()
    {
        // Load composer autoloader
        if (file_exists(__DIR__ . '/../../vendor/')) {
            require_once __DIR__ . '/../../vendor/autoload.php';
        } else {
            if (!class_exists('Cundd\\CunddComposer\\Autoloader')) {
                require_once __DIR__ . '/../../../cundd_composer/Classes/Autoloader.php';
            }
            if (!class_exists('Cundd\\CunddComposer\\Utility\\GeneralUtility')) {
                require_once __DIR__ . '/../../../cundd_composer/Classes/Utility/GeneralUtility.php';
            }
            \Cundd\CunddComposer\Autoloader::register();
        }
    }

    /**
     * Loads the TYPO3 Functional Tests bootstrap class
     *
     * @throws \Exception if the Functional Tests Bootstrap class could not be found
     */
    private function setupTYPO3()
    {
        // If TYPO3 already is loaded
        if (defined('TYPO3_MODE') && defined('ORIGINAL_ROOT')) {
            return;
        }

        $functionalTestsBootstrapPath = $this->detectFunctionalTestsBootstrapPath();
        if (false !== $functionalTestsBootstrapPath) {
            require_once $functionalTestsBootstrapPath;
        } else {
            $this->printWarning('no $functionalTestsBootstrapPath');
        }

        // Alias for typo3/testing-framework
        if (class_exists('TYPO3\TestingFramework\Core\Functional\FunctionalTestCase', true)) {
            class_alias(
                'TYPO3\TestingFramework\Core\Functional\FunctionalTestCase',
                'TYPO3\CMS\Core\Build\FunctionalTestsBootstrap'
            );
        }

        if (!class_exists('TYPO3\CMS\Core\Build\FunctionalTestsBootstrap', true)) {
            if (false === $functionalTestsBootstrapPath) {
                throw new \Exception(
                    'TYPO3\CMS\Core\Build\FunctionalTestsBootstrap not found and Bootstrap file path could not be detected'
                );
            }
            throw new \Exception('TYPO3\CMS\Core\Build\FunctionalTestsBootstrap not found');
        }
        if (!defined('ORIGINAL_ROOT')) {
            $this->printWarning('ORIGINAL_ROOT should be defined by now');
        }
    }

    /**
     * Returns the path to the Functional Tests Bootstrap file
     *
     * @return string|bool
     */
    private function detectFunctionalTestsBootstrapPath()
    {
        $typo3BasePath = $this->detectTYPO3BasePath();
        if ($typo3BasePath === false) {
            return false;
        }

        $paths = [
            'v7.x' => $typo3BasePath . '/typo3/sysext/core/Build/FunctionalTestsBootstrap.php',
            'v8.x' => $typo3BasePath . '/components/testing_framework/Resources/Core/Build/FunctionalTestsBootstrap.php',
            'v8.6' => $typo3BasePath . '/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTestsBootstrap.php',
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }

    /**
     * Returns the path to the TYPO3 installation base
     *
     * @return bool|string
     */
    private function detectTYPO3BasePath()
    {
        $restTypo3BasePath = $this->checkEnvironmentForBasePath('REST_TYPO3_BASE_PATH');
        if ($restTypo3BasePath === false) {
            $restTypo3BasePath = $this->checkEnvironmentForBasePath('TYPO3_PATH_WEB');
        }
        if ($restTypo3BasePath === false) {
            $restTypo3BasePath = $this->getTYPO3InstallationPath(realpath(__DIR__) ?: __DIR__);
        }
        if ($restTypo3BasePath === false) {
            $restTypo3BasePath = $this->getTYPO3InstallationPath(realpath(getcwd()) ?: getcwd());
        }

        return $restTypo3BasePath;
    }

    /**
     * Check the environment for a TYPO3 path variable
     *
     * @param string $environmentKey
     * @return bool|string
     */
    private function checkEnvironmentForBasePath($environmentKey)
    {
        $basePath = getenv((string)$environmentKey);
        if ($basePath === false) {
            return false;
        }

        if (file_exists($basePath)) {
            return (string)$basePath;
        }

        $this->printWarning('TYPO3 installation in %s "%s" not found', $environmentKey, $basePath);

        return false;
    }

    /**
     * Print a warning to STDERR
     *
     * @param string $message
     * @param array  ...$arguments
     */
    private function printWarning($message, ...$arguments)
    {
        fwrite(STDERR, vsprintf((string)$message, $arguments) . PHP_EOL);
    }

    /**
     * Walk the file system tree up until a TYPO3 installation is found
     *
     * @param string $startPath
     * @return string|bool Returns the path to the TYPO3 installation or FALSE if it could not be found
     */
    private function getTYPO3InstallationPath($startPath)
    {
        $cur = $startPath;
        while ($cur !== '/') {
            if (file_exists($cur . '/typo3/')) {
                return $cur;
            } elseif (file_exists($cur . '/TYPO3.CMS/typo3/')) {
                return $cur;
            }

            $cur = dirname($cur);
        }

        return false;
    }

    /**
     * Load the abstract test case
     */
    private function setupAbstractCase()
    {
        require_once __DIR__ . '/AbstractCase.php';
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new Bootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
