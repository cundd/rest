<?php

namespace Cundd\Rest;


/**
 * Session Manager
 */
class SessionManager implements SingletonInterface
{
    const KEY_PREFIX = 'rest-';

    /**
     * @var bool
     */
    protected $didInitialize = false;

    /**
     * Reads the session data from the database
     */
    protected function initialize()
    {
        if (!$this->didInitialize) {
            $frontendUserAuthentication = $this->getFrontendUserAuthentication();
            if (method_exists($frontendUserAuthentication, 'fetchSessionData')) {
                $frontendUserAuthentication->fetchSessionData();
            }

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

        return $this->getFrontendUserAuthentication()->getKey('ses', self::KEY_PREFIX . $key);
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
        $frontendUserAuthentication = $this->getFrontendUserAuthentication();
        $frontendUserAuthentication->setKey('ses', self::KEY_PREFIX . $key, $value);
        $frontendUserAuthentication->storeSessionData();

        return $this;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    private function getFrontendUserAuthentication()
    {
        return $GLOBALS['TSFE']->fe_user;
    }
}
