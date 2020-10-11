<?php
declare(strict_types=1);

namespace Cundd\Rest\Handler;

use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use Cundd\Rest\SessionManager;

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
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * Provider that will check the user credentials
     *
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * Current request
     *
     * @var RestRequestInterface
     * @deprecated will be removed in 5.0
     */
    protected $request;

    /**
     * @var RequestFactoryInterface
     * @deprecated will be removed in 5.0
     */
    protected $requestFactory;

    /**
     * AuthHandler constructor.
     *
     * @param SessionManager               $sessionManager
     * @param UserProviderInterface        $userProvider
     * @param RequestFactoryInterface|null $requestFactory
     */
    public function __construct(
        SessionManager $sessionManager,
        UserProviderInterface $userProvider,
        ?RequestFactoryInterface $requestFactory = null
    ) {
        $this->sessionManager = $sessionManager;
        $this->userProvider = $userProvider;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Return the description of the handler
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Handler for separate authorization requests';
    }

    /**
     * Returns the current status
     *
     * @return array
     */
    public function getStatus(): array
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
    public function checkLogin(RestRequestInterface $request): array
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
    public function logout(): array
    {
        $this->sessionManager->setValueForKey('loginStatus', self::STATUS_LOGGED_OUT);

        return [
            'status' => self::STATUS_LOGGED_OUT,
        ];
    }

    /**
     * @return bool
     */
    public function options(): bool
    {
        // TODO: Respond with the correct preflight headers
        return true;
    }

    /**
     * Let the handler configure the routes
     *
     * @param RouterInterface      $router
     * @param RestRequestInterface $request
     */
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();
        $router->routeGet($resourceType . '/login/?', [$this, 'getStatus']);
        $router->routePost($resourceType . '/login/?', [$this, 'checkLogin']);
        $router->add(Route::options($resourceType . '/login/?', [$this, 'options']));
        $router->routeGet($resourceType . '/logout/?', [$this, 'logout']);
        $router->routePost($resourceType . '/logout/?', [$this, 'logout']);
        $router->add(Route::options($resourceType . '/logout/?', [$this, 'options']));
    }
}
