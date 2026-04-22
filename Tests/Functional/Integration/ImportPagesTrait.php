<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

trait ImportPagesTrait
{
    public function importPages(): void
    {
        try {
            $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages-modern-typo3.csv');
        } catch (UniqueConstraintViolationException) {
        }
    }
}
