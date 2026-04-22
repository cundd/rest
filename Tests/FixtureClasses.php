<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use BadMethodCallException;
use DateTime;
use JsonSerializable;
use SplObjectStorage;
use Traversable;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;

// ClassBuilder::buildClassIfNotExists(AbstractDomainObject::class);
// ClassBuilder::buildClassIfNotExists(Repository::class);
// ClassBuilder::buildClassIfNotExists(ObjectStorage::class, SplObjectStorage::class);
// ClassBuilder::buildInterfaceIfNotExists(DomainObjectInterface::class);

/**
 * This file contains a collection of test classes
 */
class FixtureClasses
{
}

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

class MyModel extends BaseModel
{
    protected string $name = 'Initial value';

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
/**
 * @extends Repository<MyModel>
 */
class MyModelRepository extends Repository
{
}

class MyNestedModel extends BaseModel
{
    protected string $base = 'Base';

    protected DateTime $date;

    /**
     * @var BaseModel
     */
    protected mixed $child;

    public function __construct()
    {
        parent::__construct();
        $this->child = new MyModel();
        $this->date = new DateTime();
    }

    public function setBase(string $base): void
    {
        $this->base = $base;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function setChild(mixed $child): void
    {
        $this->child = $child;
    }

    public function getChild(): BaseModel
    {
        return $this->child;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}

class MyNestedModelWithObjectStorage extends MyNestedModel
{
    /**
     * @var ObjectStorage<BaseModel>|array<BaseModel>|Traversable<BaseModel>
     */
    protected iterable $children;

    /**
     * @return ObjectStorage<BaseModel>|array<BaseModel>|Traversable<BaseModel>
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    /**
     * @param iterable<BaseModel>|ObjectStorage<BaseModel>|array<BaseModel>|Traversable<BaseModel> $children
     */
    public function setChildren(iterable $children): void
    {
        $this->children = $children;
    }
}

class MyNestedJsonSerializeModel extends MyNestedModel
{
    /**
     * @return array{base:string,child:BaseModel}
     */
    public function jsonSerialize(): array
    {
        return [
            'base'  => $this->base,
            'child' => $this->child,
        ];
    }
}

class SimpleClass
{
    public mixed $firstName;

    public mixed $lastName;

    protected ?int $uid;

    protected ?int $pid;

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
}

class SimpleClassJsonSerializable extends SimpleClass implements JsonSerializable
{
    /**
     * @return array{firstName:string,lastName:string,uid:?int,pid:?int}
     */
    public function jsonSerialize(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'uid'       => $this->uid,
            'pid'       => $this->pid,
        ];
    }
}
