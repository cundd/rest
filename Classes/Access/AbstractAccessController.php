<?php

namespace Cundd\Rest\Access;

use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
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
     * @return Access
     * @throws \Exception
     */
    protected function checkAuthentication(RestRequestInterface $request)
    {
        try {
            $isAuthenticated = $this->objectManager->getAuthenticationProvider()->authenticate($request);
        } catch (\Exception $exception) {
            $this->objectManager->get(LoggerInterface::class)->logException($exception);
            throw $exception;
        }

        return $isAuthenticated === false ? Access::unauthorized() : Access::authorized();
    }
}
