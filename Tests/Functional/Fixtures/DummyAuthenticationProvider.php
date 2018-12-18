<?php
declare(strict_types=1);


namespace Cundd\Rest\Tests\Functional\Fixtures;


use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Http\RestRequestInterface;

class DummyAuthenticationProvider implements AuthenticationProviderInterface
{
    private $isAuthenticated = false;

    /**
     * DummyAuthenticationProvider constructor.
     *
     * @param bool $isAuthenticated
     */
    public function __construct($isAuthenticated)
    {
        $this->isAuthenticated = (bool)$isAuthenticated;
    }

    public function authenticate(RestRequestInterface $request)
    {
        return $this->isAuthenticated;
    }

}