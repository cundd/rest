<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'rest',
    'description'      => 'REST API for TYPO3 CMS',
    'category'         => 'services',
    'author'           => 'Daniel Corn',
    'author_email'     => 'info@cundd.net',
    'author_company'   => 'cundd',
    'state'            => 'alpha',
    'clearCacheOnLoad' => true,
    'version'          => '6.0.0-dev',
    'constraints'      => [
        'depends'   => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
