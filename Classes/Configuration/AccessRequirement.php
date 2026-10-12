<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

/**
 * Access requirement configuration for a Resource
 */
enum AccessRequirement: string
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
}
