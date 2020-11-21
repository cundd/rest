<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use TYPO3\CMS\Core\Information\Typo3Version;
use function class_exists;

trait ImportPagesTrait
{
    public function importPages()
    {
        if (class_exists(Typo3Version::class) && (new Typo3Version())->getMajorVersion() >= 9) {
            $this->importDataSet(__DIR__ . '/../Fixtures/pages-modern-typo3.xml');
        } else {
            $this->importDataSet('ntf://Database/pages.xml');
            $this->importDataSet('ntf://Database/pages_language_overlay.xml');
        }
    }

    public function importPagesWithRootId10()
    {
        if (class_exists(Typo3Version::class) && (new Typo3Version())->getMajorVersion() >= 9) {
            $this->importDataSet(__DIR__ . '/../Fixtures/pages-root-not-1-modern-typo3.xml');
        } else {
            $this->importDataSet(__DIR__ . '/../Fixtures/pages-root-not-1.xml');
            $this->importDataSet(__DIR__ . '/../Fixtures/pages_language_overlay-root-not-1.xml');
        }
    }
}
