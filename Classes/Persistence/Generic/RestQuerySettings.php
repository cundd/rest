<?php

namespace Cundd\Rest\Persistence\Generic;

use TYPO3\CMS\Extbase\Persistence\Generic as TYPO3Generic;

class RestQuerySettings extends TYPO3Generic\Typo3QuerySettings implements TYPO3Generic\QuerySettingsInterface
{
    /**
     * Flag if the storage page should be respected for the query.
     *
     * @var boolean
     */
    protected $respectStoragePage = false;
}
