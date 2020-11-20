<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'rest',
    'description'      => 'REST API for TYPO3 CMS',
    'category'         => 'services',
    'author'           => 'Daniel Corn',
    'author_email'     => 'info@cundd.net',
    'author_company'   => 'cundd',
    'state'            => 'stable',
    'uploadfolder'     => '0',
    'createDirs'       => '',
    'clearCacheOnLoad' => true,
    'version'          => '5.0.0-dev',
    'constraints'      => [
        'depends'   => [
            'typo3' => '8.7.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
