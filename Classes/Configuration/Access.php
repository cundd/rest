<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use Cundd\Rest\Exception\InvalidArgumentException;

/**
 * Object to hold the access requirements for a Resource
 *
 * The identifier signals if a request is allowed
 */
class Access
{
    /**
     * Access identifier to signal denied requests
     */
    private const ACCESS_DENY = 'deny';

    /**
     * Access identifier to signal allowed requests
     */
    private const ACCESS_ALLOW = 'allow';

    /**
     * Access identifier to signal requests that require a valid login
     */
    private const ACCESS_REQUIRE_LOGIN = 'require';

    /**
     * Access identifier to signal a successful login
     */
    private const ACCESS_AUTHORIZED = self::ACCESS_ALLOW;

    /**
     * Access identifier to signal a missing or failed login
     */
    private const ACCESS_UNAUTHORIZED = 'unauthorized';

    /**
     * @var string
     */
    private $value;

    /**
     * Access constructor.
     *
     * @param string|Access $value
     */
    public function __construct($value)
    {
        if (!($value instanceof Access) && !is_string($value)) {
            throw InvalidArgumentException::buildException($value, 'string|' . Access::class, 'value');
        }

        $valueString = (string)$value;
        if ($valueString !== self::ACCESS_ALLOW
            && $valueString !== self::ACCESS_DENY
            && $valueString !== self::ACCESS_REQUIRE_LOGIN
            && $valueString !== self::ACCESS_AUTHORIZED
            && $valueString !== self::ACCESS_UNAUTHORIZED) {
            throw new InvalidArgumentException('Argument value must be one of the ACCESS constants');
        }

        $this->value = $valueString;
    }

    /**
     * Return a new instance with `ACCESS_DENY` state
     *
     * @return Access
     */
    public static function denied(): self
    {
        return new static(self::ACCESS_DENY);
    }

    /**
     * Return a new instance with `ACCESS_ALLOW` state
     *
     * @return Access
     */
    public static function allowed(): self
    {
        return new static(self::ACCESS_ALLOW);
    }

    /**
     * Return a new instance with `ACCESS_REQUIRE_LOGIN` state
     *
     * @return Access
     */
    public static function requiresLogin(): self
    {
        return new static(self::ACCESS_REQUIRE_LOGIN);
    }

    /**
     * Return a new instance with `ACCESS_AUTHORIZED` state
     *
     * @return Access
     */
    public static function authorized(): self
    {
        return new static(self::ACCESS_AUTHORIZED);
    }

    /**
     * Return a new instance with `ACCESS_UNAUTHORIZED` state
     *
     * @return Access
     */
    public static function unauthorized(): self
    {
        return new static(self::ACCESS_UNAUTHORIZED);
    }

    public function isAllowed(): bool
    {
        return $this->value === self::ACCESS_ALLOW;
    }

    public function isDenied(): bool
    {
        return $this->value === self::ACCESS_DENY;
    }

    public function isRequireLogin(): bool
    {
        return $this->value === self::ACCESS_REQUIRE_LOGIN;
    }

    public function isAuthorized(): bool
    {
        return $this->value === self::ACCESS_AUTHORIZED;
    }

    public function isUnauthorized(): bool
    {
        return $this->value === self::ACCESS_UNAUTHORIZED;
    }

    public function __toString()
    {
        return $this->value;
    }
}
