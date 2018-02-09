<?php

namespace Cundd\Rest\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

/**
 * Handler for the credentials authorization
 */
class AuthHandler implements HandlerInterface, HandlerDescriptionInterface
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
     * Return the description of the handler
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Handler for separate authorization requests';
    }


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

        return [
            'status' => $loginStatus,
        ];
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

        return [
            'status' => $loginStatus,
        ];
    }

    /**
     * Log out
     *
     * @return array
     */
    public function logout()
    {
        $this->sessionManager->setValueForKey('loginStatus', self::STATUS_LOGGED_OUT);

        return [
            'status' => self::STATUS_LOGGED_OUT,
        ];
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
