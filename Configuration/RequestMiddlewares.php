<?php

use Cundd\Rest\Http\RestMiddleware;

return [
    'frontend' => [
        'cundd/rest/rest-middleware' => [
            'target' => RestMiddleware::class,
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/page-resolver',
            ],
            'after'  => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
