<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'rest',
    'description'      => 'REST API for TYPO3 CMS',
    'category'         => 'services',
    'author'           => 'Daniel Corn',
    'author_email'     => 'info@cundd.net',
    'author_company'   => 'cundd',
    'state'            => 'alpha',
    'uploadfolder'     => '0',
    'createDirs'       => '',
    'clearCacheOnLoad' => true,
    'version'          => '5.1.0-dev',
    'constraints'      => [
        'depends'   => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
