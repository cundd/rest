<?php
/**
 * Unit Test bootstrapping
 */

namespace Cundd\Rest\Tests;

class Bootstrap {
    const TYPO3_BOOTSTRAP_CLASS_PATH = 'sysext/core/Build/FunctionalTestsBootstrap.php';

    public function bootstrapSystem() {
        $this->setupTYPO3();
        $this->setupComposer();
        $this->setupAbstractCase();
    }

    protected function setupTYPO3() {
        if (defined('TYPO3_MODE')) {
            return;
        }
        $restTypo3BasePath = getenv('REST_TYPO3_BASE_PATH');
        if ($restTypo3BasePath === false) {
            $restTypo3BasePath = getenv('TYPO3_PATH_WEB');
        }
        if ($restTypo3BasePath) {
            if (file_exists($restTypo3BasePath . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH)) {
                require_once $restTypo3BasePath . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH;
            } else {
                throw new \RuntimeException(sprintf(
                    'Directory "typo3/" not found in given REST_TYPO3_BASE_PATH "%s"',
                    $restTypo3BasePath
                ));
            }
        } elseif (file_exists(getcwd() . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH)) {
            require_once getcwd() . '/typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH;
        } elseif (file_exists(__DIR__ . '/../../../../typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH)) {
            require_once __DIR__ . '/../../../../typo3/' . self::TYPO3_BOOTSTRAP_CLASS_PATH;
        } else {
            return;
        }

        if (class_exists('TYPO3\CMS\Core\Build\FunctionalTestsBootstrap')) {
            $bootstrap = new \TYPO3\CMS\Core\Build\FunctionalTestsBootstrap();
            $bootstrap->bootstrapSystem();
            unset($bootstrap);
        }
    }

    protected function setupComposer() {
        // Load composer autoloader
        if (file_exists(__DIR__ . '/../vendor/')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        } else {
            if (!class_exists('Cundd\\CunddComposer\\Autoloader')) {
                require_once __DIR__ . '/../../cundd_composer/Classes/Autoloader.php';
            }
            if (!class_exists('Cundd\\CunddComposer\\Utility\\GeneralUtility')) {
                require_once __DIR__ . '/../../cundd_composer/Classes/Utility/GeneralUtility.php';
            }
            \Cundd\CunddComposer\Autoloader::register();
        }
    }

    private function setupAbstractCase() {
        require_once __DIR__ . '/Functional/AbstractCase.php';
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new Bootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);

