<?php

namespace Cundd\Rest\Access;

use Cundd\Rest\Dispatcher;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManager;

abstract class AbstractAccessController implements AccessControllerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * AbstractAccessController constructor
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Checks if a valid user is logged in
     *
     * @param RestRequestInterface $request
     * @return string
     * @throws \Exception
     */
    protected function checkAuthentication(RestRequestInterface $request)
    {
        try {
            $isAuthenticated = $this->objectManager->getAuthenticationProvider()->authenticate($request);
        } catch (\Exception $exception) {
            Dispatcher::getSharedDispatcher()->logException($exception);
            throw $exception;
        }
        if ($isAuthenticated === false) {
            return self::ACCESS_UNAUTHORIZED;
        }

        return self::ACCESS_ALLOW;
    }
}
