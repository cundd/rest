<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

/**
 * Access requirement configuration and resolved identifier
 */
enum Access: string
{
    /**
     * Access identifier to signal denied requests
     */
    case Denied = 'deny';

    /**
     * Access identifier to signal allowed requests
     */
    case Allowed = 'allow';

    /**
     * Access identifier to signal requests that require a valid login
     */
    case RequireLogin = 'require';

    /**
     * Access identifier to signal a successful login
     */
    case Authorized = 'authorized';

    /**
     * Access identifier to signal a missing or failed login
     */
    case Unauthorized = 'unauthorized';
}
