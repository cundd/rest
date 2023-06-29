<?php

declare(strict_types=1);

namespace Cundd\Rest;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Session Manager
 */
class SessionManager implements SingletonInterface
{
    const KEY_PREFIX = 'rest-';

    /**
     * Returns the value for the given key
     *
     * @param string $key
     * @return mixed
     */
    public function valueForKey(string $key)
    {
        return $this->getFrontendUserAuthentication()->getKey('ses', self::KEY_PREFIX . $key);
    }

    /**
     * Sets the value for the given key
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setValueForKey(string $key, $value): self
    {
        $frontendUserAuthentication = $this->getFrontendUserAuthentication();
        $frontendUserAuthentication->setKey('ses', self::KEY_PREFIX . $key, $value);
        $frontendUserAuthentication->storeSessionData();

        return $this;
    }

    /**
     * @return FrontendUserAuthentication
     */
    private function getFrontendUserAuthentication(): object
    {
        return $GLOBALS['TSFE']->fe_user;
    }
}
