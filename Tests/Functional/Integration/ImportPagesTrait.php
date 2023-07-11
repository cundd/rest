<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

trait ImportPagesTrait
{
    public function importPages(): void
    {
        try {
            $this->importDataSet(__DIR__ . '/../Fixtures/pages-modern-typo3.xml');
        } catch (UniqueConstraintViolationException) {
        }
    }

    public function importPagesWithRootId10(): void
    {
        try {
            $this->importDataSet(__DIR__ . '/../Fixtures/pages-root-not-1-modern-typo3.xml');
        } catch (UniqueConstraintViolationException) {
        }
    }
}
