<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 15.05.13
 * Time: 20:01
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Persistence\Generic;

use \TYPO3\CMS\Extbase\Persistence\Generic as TYPO3Generic;

class RestQuerySettings extends TYPO3Generic\Typo3QuerySettings implements TYPO3Generic\QuerySettingsInterface {
	/**
	 * Flag if the storage page should be respected for the query.
	 *
	 * @var boolean
	 */
	protected $respectStoragePage = FALSE;
}