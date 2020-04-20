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
    'version'          => '3.6.1',
    'constraints'      => [
        'depends'   => [
            'typo3' => '7.6.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
