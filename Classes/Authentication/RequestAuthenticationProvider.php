<?php

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;

/**
 * The class expects an existing valid authenticated Frontend User or credentials passed through the request.
 *
 * Example URL: logintype=login&pid=PIDwhereTheFEUsersAreStored&user=MyUserName&pass=MyPassword
 */
class RequestAuthenticationProvider extends AbstractAuthenticationProvider
{
    /**
     * Tries to authenticate the current request
     *
     * @param RestRequestInterface $request
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request)
    {
        return !!($GLOBALS['TSFE']->fe_user->user);
    }
}
