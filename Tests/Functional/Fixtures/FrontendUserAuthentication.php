<?php


namespace Cundd\Rest\Tests\Functional\Fixtures;


class FrontendUserAuthentication
{
    private static $cache = [];

    public static function reset()
    {
        static::$cache = [];
    }

    /**
     * Returns session data for the fe_user; Either persistent data following the fe_users uid/profile (requires login)
     * or current-session based (not available when browse is closed, but does not require login)
     *
     * @param string $type Session data type; Either "user" (persistent, bound to fe_users profile) or "ses" (temporary, bound to current session cookie)
     * @param string $key  Key from the data array to return; The session data (in either case) is an array (static::$uc / static::$sessionData) and this value determines which key to return the value for.
     * @return mixed Returns whatever value there was in the array for the key, $key
     * @see setKey()
     */
    public function getKey($type, $key)
    {
        return isset(static::$cache[$type . $key]) ? static::$cache[$type . $key] : null;
    }

    /**
     * Saves session data, either persistent or bound to current session cookie. Please see getKey() for more details.
     * When a value is set the flags static::$userData_change or static::$sesData_change will be set so that the final call to ->storeSessionData() will know if a change has occurred and needs to be saved to the database.
     * Notice: The key "recs" is already used by the function record_registration() which stores table/uid=value pairs in that key. This is used for the shopping basket among other things.
     * Notice: Simply calling this function will not save the data to the database! The actual saving is done in storeSessionData() which is called as some of the last things in \TYPO3\CMS\Frontend\Http\RequestHandler. So if you exit before this point, nothing gets saved of course! And the solution is to call $GLOBALS['TSFE']->storeSessionData(); before you exit.
     *
     * @param string $type Session data type; Either "user" (persistent, bound to fe_users profile) or "ses" (temporary, bound to current session cookie)
     * @param string $key  Key from the data array to store incoming data in; The session data (in either case) is an array (static::$uc / static::$sessionData) and this value determines in which key the $data value will be stored.
     * @param mixed  $data The data value to store in $key
     * @see setKey(), storeSessionData(), record_registration()
     */
    public function setKey($type, $key, $data)
    {
        static::$cache[$type . $key] = $data;
    }

    /**
     * Will write UC and session data.
     * If the flag static::$userData_change has been set, the function ->writeUC is called (which will save persistent user session data)
     * If the flag static::$sesData_change has been set, the current session record is updated with the content of static::$sessionData
     *
     * @see getKey(), setKey()
     */
    public function storeSessionData()
    {
    }
}
