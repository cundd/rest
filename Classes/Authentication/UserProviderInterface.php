<?php

/*
 * rest
 * @author daniel
 * Date: 14.09.13
 * Time: 18:52
 * @license MIT
 */

namespace Cundd\Rest\Authentication;

interface UserProviderInterface
{
    /**
     * Returns if the user with the given credentials is valid
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function checkCredentials($username, $password);
}
