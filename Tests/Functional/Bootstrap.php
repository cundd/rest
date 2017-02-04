<?php
/**
 * Functional Test bootstrapping
 */

namespace Cundd\Rest\Tests\Functional;

class Bootstrap
{
    const TYPO3_BOOTSTRAP_CLASS_PATH = 'sysext/core/Build/FunctionalTestsBootstrap.php';

    public function bootstrapSystem()
    {
        $this->setupComposer();
        $this->setupTYPO3();
        $this->setupAbstractCase();
    }

    protected function setupTYPO3()
    {
        if (defined('TYPO3_MODE') && defined('ORIGINAL_ROOT')) {
            return;
        }
        $restTypo3BasePath = $this->detectTypo3BasePath();

        if ($restTypo3BasePath !== false) {
            $typo3Version8LtsPath = $restTypo3BasePath . '/components/testing_framework/Resources/Core/Build/FunctionalTestsBootstrap.php';

            if (file_exists($restTypo3BasePath . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH)) {
                require_once $restTypo3BasePath . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH;

                return;
            } elseif (file_exists($typo3Version8LtsPath)) {
                require_once $typo3Version8LtsPath;

                return;
            }
        }

        if (!class_exists('TYPO3\CMS\Core\Build\FunctionalTestsBootstrap')) {
            throw new \Exception('TYPO3\CMS\Core\Build\FunctionalTestsBootstrap not found');
        }
    }

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

    private function setupAbstractCase()
    {
        require_once __DIR__ . '/AbstractCase.php';
    }

    private function getTYPO3InstallationPath($startPath)
    {
        $cur = $startPath;
        while ($cur !== '/') {
            if (file_exists($cur . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH)) {
                return $cur . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH;
            } elseif (file_exists($cur . '/TYPO3.CMS/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH)) {
                return $cur . '/TYPO3.CMS/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH;
            }

            $cur = dirname($cur);
        }

        return '';
    }

    /**
     * @param string $environmentKey
     * @return array|bool|false|string
     */
    protected function checkEnvironmentForTypo3BasePath($environmentKey)
    {
        $restTypo3BasePath = getenv((string)$environmentKey);
        if ($restTypo3BasePath === false) {
            return false;
        }

        if (file_exists($restTypo3BasePath)) {
            return $restTypo3BasePath;
        }

        $this->printWarning('TYPO3 installation in %s "%s" not found', $environmentKey, $restTypo3BasePath);

        return false;
    }

    /**
     * @return array|bool|false|string
     */
    protected function detectTypo3BasePath()
    {
        $restTypo3BasePath = $this->checkEnvironmentForTypo3BasePath('REST_TYPO3_BASE_PATH');
        if ($restTypo3BasePath === false) {
            $restTypo3BasePath = $this->checkEnvironmentForTypo3BasePath('TYPO3_PATH_WEB');
        }
        if ($restTypo3BasePath === false) {
            $restTypo3BasePath = $this->getTYPO3InstallationPath(realpath(__DIR__) ?: __DIR__);
        }
        if ($restTypo3BasePath === false) {
            $restTypo3BasePath = $this->getTYPO3InstallationPath(realpath(getcwd()) ?: getcwd());

            return $restTypo3BasePath;
        }

        return $restTypo3BasePath;
    }

    private function printWarning($message, ...$arguments)
    {
        fwrite(STDERR, vsprintf((string)$message, $arguments) . PHP_EOL);
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new Bootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
