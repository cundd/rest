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
	 * Returns the value for the given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function valueForKey($key) {
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