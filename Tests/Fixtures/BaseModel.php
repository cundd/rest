<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

use BadMethodCallException;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

/**
 * @method ?int getUid()
 * @method _setProperty(string $name,mixed $value)
 * @method array<string,mixed> _getProperties()
 */
class BaseModel extends AbstractDomainObject implements DomainObjectInterface
{
    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function __wakeup(): void
    {
        // Prevent calling GeneralUtility::logDeprecatedFunction();
    }

    /**
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $getUid = function () {
            return $this->uid;
        };

        /**
         * Reconstitutes a property. Only for internal use.
         *
         * @param string $propertyName
         * @param mixed  $propertyValue
         *
         * @return bool
         */
        $_setProperty = function ($propertyName, $propertyValue) {
            if (property_exists($this, $propertyName)) {
                $this->{$propertyName} = $propertyValue;

                return true;
            }

            return false;
        };

        /**
         * Returns a hash map of property names and property values. Only for internal use.
         *
         * @return array The properties
         */
        $_getProperties = function () {
            $properties = get_object_vars($this);
            foreach ($properties as $propertyName => $propertyValue) {
                if ('_' === $propertyName[0]) {
                    unset($properties[$propertyName]);
                }
            }

            return $properties;
        };

        if ('getUid' === $name) {
            return $getUid();
        }
        if ('_setProperty' === $name) {
            return $_setProperty(...$arguments);
        }
        if ('_getProperties' === $name) {
            return $_getProperties();
        }
        throw new BadMethodCallException();
    }
}
