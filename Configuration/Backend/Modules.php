<?php

declare(strict_types=1);

use Cundd\Rest\Controller\ReportController;
use TYPO3\CMS\Core\Information\Typo3Version;

if (14 == (new Typo3Version())->getMajorVersion()) {
    return [
        'system_reports_rest' => [
            'parent'         => 'system_reports',
            'access'         => 'admin',
            'path'           => '/module/system/reports/rest',
            'iconIdentifier' => 'module-reports',
            'labels'         => [
                'title'       => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.title',
                'description' => 'LLL:EXT:rest/Resources/Private/Language/locallang_db.xlf:reports.handler.description',
            ],
            'routes' => [
                '_default' => [
                    'target' => ReportController::class . '::handleRequest',
                ],
            ],
        ],
    ];
} else {
    return [];
}
