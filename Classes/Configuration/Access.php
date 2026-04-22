<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\Exception\InvalidArgumentException;

/**
 * Object to hold the access requirements for a Resource
 *
 * The identifier signals if a request is allowed
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

    // public function __construct(string|Access $value)
    // {
    // // dontcommit
    //     $valueString = (string) $value;
    //     if (self::ACCESS_ALLOW !== $valueString
    //         && self::ACCESS_DENY !== $valueString
    //         && self::ACCESS_REQUIRE_LOGIN !== $valueString
    //         && self::ACCESS_AUTHORIZED !== $valueString
    //         && self::ACCESS_UNAUTHORIZED !== $valueString) {
    //         throw new InvalidArgumentException('Argument value must be one of the ACCESS constants');
    //     }
    //
    //     $this->value = $valueString;
    // }

    // /**
    //  * Return a new instance with `ACCESS_DENY` state
    //  *
    //  * @deprecated
    //  */
    // public static function denied(): self
    // {
    //     return self::Denied;
    // }
    //
    // /**
    //  * Return a new instance with `ACCESS_ALLOW` state
    //  *
    //  * @deprecated
    //  */
    // public static function allowed(): self
    // {
    //     return (self::_ALLOW);
    // }
    //
    // /**
    //  * Return a new instance with `ACCESS_REQUIRE_LOGIN` state
    //  *
    //  * @deprecated
    //  */
    // public static function requiresLogin(): self
    // {
    //     return (self::ACCESS_REQUIRE_LOGIN);
    // }
    //
    // /**
    //  * Return a new instance with `ACCESS_AUTHORIZED` state
    //  *
    //  * @deprecated
    //  */
    // public static function authorized(): self
    // {
    //     return (self::ACCESS_AUTHORIZED);
    // }
    //
    // /**
    //  * Return a new instance with `ACCESS_UNAUTHORIZED` state
    //  *
    //  * @deprecated
    //  */
    // public static function unauthorized(): self
    // {
    //     return (self::ACCESS_UNAUTHORIZED);
    // }
    //
    // public function isAllowed(): bool
    // {
    //     return self::ACCESS_ALLOW === $this->value;
    // }
    //
    // public function isDenied(): bool
    // {
    //     return self::ACCESS_DENY === $this->value;
    // }
    //
    // public function isRequireLogin(): bool
    // {
    //     return self::ACCESS_REQUIRE_LOGIN === $this->value;
    // }
    //
    // public function isAuthorized(): bool
    // {
    //     return self::ACCESS_AUTHORIZED === $this->value;
    // }
    //
    // public function isUnauthorized(): bool
    // {
    //     return self::ACCESS_UNAUTHORIZED === $this->value;
    // }
    //
    // public function __toString(): string
    // {
    //     return $this->value;
    // }
}
