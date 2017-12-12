<?php
/*
*  Copyright notice
*
*  (c) 2014 Daniel Corn <info@cundd.net>, cundd
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*/

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;

/**
 * Authentication Provider for login data sent through Basic Auth
 */
class BasicAuthenticationProvider extends AbstractAuthenticationProvider
{
    /**
     * Provider that will check the user credentials
     *
     * @var \Cundd\Rest\Authentication\UserProviderInterface
     * @inject
     */
    protected $userProvider;

    /**
     * Tries to authenticate the current request
     *
     * @param RestRequestInterface $request
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request)
    {
        $username = null;
        $password = null;

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
        } elseif ($tuple = $this->checkServerData('HTTP_AUTHENTICATION')) {
            list($username, $password) = $tuple;
        } elseif ($tuple = $this->checkServerData('HTTP_AUTHORIZATION')) {
            list($username, $password) = $tuple;
        } elseif ($tuple = $this->checkServerData('REDIRECT_HTTP_AUTHORIZATION')) {
            list($username, $password) = $tuple;
        }

        return $this->userProvider->checkCredentials($username, $password);
    }

    private function checkServerData($key)
    {
        if (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
            if (strpos(strtolower($value), 'basic') === 0) {
                return explode(':', base64_decode(substr($value, 6)));
            }
        }

        return [];
    }
}
