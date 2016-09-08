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
class SessionManager implements SingletonInterface
{
    const KEY_PREFIX = 'rest-';

    /**
     * @var bool
     */
    private $didInitialize = false;

    /**
     * Reads the session data from the database
     */
    private function initialize()
    {
        if (!$this->didInitialize) {
            $this->getFrontendUser()->fetchSessionData();
            $this->didInitialize = true;
        }
    }

    /**
     * Returns the value for the given key
     *
     * @param string $key
     * @return mixed
     */
    public function valueForKey($key)
    {
        $this->initialize();

        return $this->getFrontendUser()->getKey('ses', self::KEY_PREFIX . $key);
    }

    /**
     * Sets the value for the given key
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setValueForKey($key, $value)
    {
        $this->getFrontendUser()->setKey('ses', self::KEY_PREFIX . $key, $value);
        $this->getFrontendUser()->storeSessionData();

        return $this;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    private function getFrontendUser()
    {
        return $GLOBALS['TSFE']->fe_user;
    }
}
