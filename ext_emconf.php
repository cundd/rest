<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'rest',
    'description'      => 'REST API for TYPO3 CMS',
    'category'         => 'services',
    'author'           => 'Daniel Corn',
    'author_email'     => 'info@cundd.net',
    'author_company'   => 'cundd',
    'state'            => 'stable',
    'clearCacheOnLoad' => true,
    'version'          => '6.0.0',
    'constraints'      => [
        'depends'   => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
