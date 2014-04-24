<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 01.04.14
 * Time: 22:10
 */

namespace Cundd\Rest;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Session Manager
 *
 * @package Cundd\Rest
 */
class SessionManager implements SingletonInterface {
	const KEY_PREFIX = 'rest-';

	/**
	 * @var bool
	 */
	protected $didInitialize = FALSE;

	/**
	 * Reads the session data from the database
	 */
	protected function _initialize() {
		if (!$this->didInitialize) {
			$GLOBALS['TSFE']->fe_user->fetchSessionData();
			$this->didInitialize = TRUE;
		}
	}

	/**
	 * Returns the value for the given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function valueForKey($key) {
		$this->_initialize();
		return $GLOBALS['TSFE']->fe_user->getKey('ses', self::KEY_PREFIX . $key);
	}

	/**
	 * Sets the value for the given key
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return $this
	 */
	public function setValueForKey($key, $value) {
		$GLOBALS['TSFE']->fe_user->setKey('ses', self::KEY_PREFIX . $key, $value);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
		return $this;
	}
}