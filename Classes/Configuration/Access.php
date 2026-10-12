<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

/**
 * Resolved access rights for a Resource
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
     * Access identifier to signal a successful login
     */
    case Authorized = 'authorized';

    /**
     * Access identifier to signal a missing or failed login
     */
    case Unauthorized = 'unauthorized';
}
