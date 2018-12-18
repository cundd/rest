<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional;

use Cundd\Rest\Tests\AbstractBootstrap;

require_once __DIR__ . '/../AbstractBootstrap.php';

/**
 * Bootstrap for functional tests
 */
class Bootstrap extends AbstractBootstrap
{
    /**
     * Bootstrap the TYPO3 system
     */
    protected function bootstrapSystem()
    {
        $this->setupTYPO3();
        $this->setupAbstractCase();
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
        if (false === $functionalTestsBootstrapPath) {
            $this->printWarning('Could not detect the path to the Functional Tests Bootstrap file');
        } else {
            require_once $functionalTestsBootstrapPath;
        }

        $this->prepareTestBaseClass();

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
            $this->printWarning('Could not detect TYPO3 base path');

            return false;
        }

        $paths = [
            'v7.x' => $typo3BasePath . '/typo3/sysext/core/Build/FunctionalTestsBootstrap.php',
            'v8.6' => $typo3BasePath . '/components/testing_framework/Resources/Core/Build/FunctionalTestsBootstrap.php',
            'v8.7' => $typo3BasePath . '/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTestsBootstrap.php',
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
        $typo3BasePath = $this->checkEnvironmentForBasePath('REST_TYPO3_BASE_PATH');
        if ($typo3BasePath === false) {
            $typo3BasePath = $this->checkEnvironmentForBasePath('TYPO3_PATH_WEB');
        }
        if ($typo3BasePath === false) {
            $typo3BasePath = $this->getTYPO3InstallationPath(realpath(__DIR__) ?: __DIR__);
        }
        if ($typo3BasePath === false) {
            $typo3BasePath = $this->getTYPO3InstallationPath(realpath(getcwd()) ?: getcwd());
        }

        return $typo3BasePath;
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
                return $cur . '/TYPO3.CMS';
            }

            $cur = dirname($cur);
        }

        return false;
    }

    /**
     * Creates an alias for the Testing Framework if needed
     */
    private function prepareTestBaseClass()
    {
        if (!class_exists('TYPO3\CMS\Core\Tests\FunctionalTestCase')
            && class_exists('TYPO3\TestingFramework\Core\Functional\FunctionalTestCase', true)
        ) {
            class_alias(
                'TYPO3\TestingFramework\Core\Functional\FunctionalTestCase',
                'TYPO3\CMS\Core' . '\Tests\FunctionalTestCase'
            );
        }
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
$bootstrap->run();
unset($bootstrap);
