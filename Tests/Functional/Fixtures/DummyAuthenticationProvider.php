<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Fixtures;

use Cundd\Rest\Authentication\AuthenticationProviderInterface;
use Cundd\Rest\Http\RestRequestInterface;

final readonly class DummyAuthenticationProvider implements AuthenticationProviderInterface
{
    public function __construct(private bool $isAuthenticated)
    {
    }

    public function authenticate(RestRequestInterface $request): bool
    {
        return $this->isAuthenticated;
    }
}
