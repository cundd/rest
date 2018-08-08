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
    'version'          => '3.3.1-dev',
    'constraints'      => [
        'depends'   => [
            'typo3' => '7.6.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
