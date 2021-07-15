<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' =>
        [
            'title'         => 'LLL:EXT:custom_rest/Resources/Private/Language/locallang_db.xlf:tx_customrest_domain_model_person',
            'label'         => 'first_name',
            'tstamp'        => 'tstamp',
            'crdate'        => 'crdate',
            'cruser_id'     => 'cruser_id',
            'dividers2tabs' => true,

            'versioningWS' => true,

            'languageField'            => 'sys_language_uid',
            'transOrigPointerField'    => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'delete'                   => 'deleted',
            'enablecolumns'            => [
                'disabled'  => 'hidden',
                'starttime' => 'starttime',
                'endtime'   => 'endtime',
            ],
            'searchFields'             => 'first_name,last_name,birthday,',
            'iconfile'                 => 'EXT:custom_rest/Resources/Public/Icons/tx_customrest_domain_model_person.gif',
        ],

    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, first_name, last_name, birthday',
    ],
    'types'     => [
        '1' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, first_name, last_name, birthday, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'palettes'  => [
        '1' => ['showitem' => ''],
    ],

    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'special'    => 'languages',
                'items'      => [
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple',
                    ],
                ],
                'default'    => 0,
            ],
        ],
        'l10n_parent'      => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => true,
            'label'       => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config'      => [
                'type'                => 'select',
                'renderType'          => 'selectSingle',
                'default'             => 0,
                'items'               => [
                    ['', 0],
                ],
                'foreign_table'       => 'tx_customrest_domain_model_person',
                'foreign_table_where' => 'AND tx_customrest_domain_model_person.pid=###CURRENT_PID### AND tx_customrest_domain_model_person.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource'  => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label'      => [
            'label'  => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max'  => 255,
            ],
        ],
        'hidden'           => [
            'exclude' => true,
            'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config'  => [
                'type'  => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:lang/locallang_core.xlf:labels.enabled',
                    ],
                ],
            ],
        ],
        'starttime'        => [
            'exclude'   => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true,
            ],
            'label'     => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config'    => [
                'type'       => 'input',
                'renderType' => 'inputDateTime',
                'size'       => 13,
                'eval'       => 'datetime',
                'default'    => 0,
            ],
        ],
        'endtime'          => [
            'exclude'   => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true,
            ],
            'label'     => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config'    => [
                'type'       => 'input',
                'renderType' => 'inputDateTime',
                'size'       => 13,
                'eval'       => 'datetime',
                'default'    => 0,
                'range'      => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
        ],
        'first_name'       => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:custom_rest/Resources/Private/Language/locallang_db.xlf:tx_customrest_domain_model_person.first_name',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'last_name'        => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:custom_rest/Resources/Private/Language/locallang_db.xlf:tx_customrest_domain_model_person.last_name',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'birthday' => [
            'exclude' => true,
            'label' => 'LLL:EXT:custom_rest2/Resources/Private/Language/locallang_db.xlf:tx_customrest_domain_model_person.birthday',
            'config' => [
                'dbType' => 'date',
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 7,
                'eval' => 'date',
                'default' => null,
            ],
        ],
    ],
];
