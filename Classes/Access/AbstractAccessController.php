<?php

declare(strict_types=1);

namespace Cundd\Rest\Access;

use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Exception;

abstract class AbstractAccessController implements AccessControllerInterface
{
    public function __construct(protected ObjectManagerInterface $objectManager)
    {
    }

    /**
     * Checks if a valid user is logged in
     *
     * @param RestRequestInterface $request
     * @return Access
     * @throws Exception
     */
    protected function checkAuthentication(RestRequestInterface $request): Access
    {
        try {
            $isAuthenticated = $this->objectManager->getAuthenticationProvider($request)->authenticate($request);
        } catch (Exception $exception) {
            $this->objectManager->get(LoggerInterface::class)->logException($exception);
            throw $exception;
        }

        return $isAuthenticated === false ? Access::unauthorized() : Access::authorized();
    }
}
