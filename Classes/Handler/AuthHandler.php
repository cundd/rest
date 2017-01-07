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

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 01.04.14
 * Time: 21:55
 */

namespace Cundd\Rest\Handler;

use Cundd\Rest\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

/**
 * Handler for the credentials authorization
 */
class AuthHandler implements HandlerInterface
{
    /**
     * Status logged in
     */
    const STATUS_LOGGED_IN = 'logged-in';

    /**
     * Status logged out
     */
    const STATUS_LOGGED_OUT = 'logged-out';

    /**
     * Status failed login attempt
     */
    const STATUS_FAILURE = 'login failure';

    /**
     * Current request
     *
     * @var RestRequestInterface
     */
    protected $request;

    /**
     * @var \Cundd\Rest\SessionManager
     * @inject
     */
    protected $sessionManager;

    /**
     * Provider that will check the user credentials
     *
     * @var \Cundd\Rest\Authentication\UserProviderInterface
     * @inject
     */
    protected $userProvider;

    /**
     * @var \Cundd\Rest\RequestFactoryInterface
     * @inject
     */
    protected $requestFactory;

    /**
     * Returns the current status
     *
     * @return array
     */
    public function getStatus()
    {
        $loginStatus = $this->sessionManager->valueForKey('loginStatus');
        if ($loginStatus === null) {
            $loginStatus = self::STATUS_LOGGED_OUT;
        }

        return array(
            'status' => $loginStatus,
        );
    }

    /**
     * Check the given login data
     *
     * @param RestRequestInterface $request
     * @return array
     * @internal param array $sentData
     */
    public function checkLogin(RestRequestInterface $request)
    {
        $sentData = $request->getSentData();
        $loginStatus = self::STATUS_LOGGED_OUT;
        if (isset($sentData['username']) && isset($sentData['apikey'])) {
            $username = $sentData['username'];
            $apiKey = $sentData['apikey'];

            if ($this->userProvider->checkCredentials($username, $apiKey)) {
                $loginStatus = self::STATUS_LOGGED_IN;
            } else {
                $loginStatus = self::STATUS_FAILURE;
            }
            $this->sessionManager->setValueForKey('loginStatus', $loginStatus);
        }

        return array(
            'status' => $loginStatus,
        );
    }

    /**
     * Log out
     *
     * @return array
     */
    public function logout()
    {
        $this->sessionManager->setValueForKey('loginStatus', self::STATUS_LOGGED_OUT);

        return array(
            'status' => self::STATUS_LOGGED_OUT,
        );
    }

    /**
     * Let the handler configure the routes
     *
     * @param RouterInterface      $router
     * @param RestRequestInterface $request
     */
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $router->routeGet($request->getResourceType() . '/login/?', [$this, 'getStatus']);
        $router->routePost($request->getResourceType() . '/login/?', [$this, 'checkLogin']);
        $router->routeGet($request->getResourceType() . '/logout/?', [$this, 'logout']);
        $router->routePost($request->getResourceType() . '/logout/?', [$this, 'logout']);
    }
}
