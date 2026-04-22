<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;
use TYPO3\CMS\Core\Context\Context;

/**
 * The class expects an existing valid authenticated Frontend User
 */
class RequestAuthenticationProvider implements AuthenticationProviderInterface
{
    public function __construct(private readonly Context $context)
    {
    }

    public function authenticate(RestRequestInterface $request): bool
    {
        return $this->context->getPropertyFromAspect(
            'frontend.user',
            'isLoggedIn'
        );
    }
}
