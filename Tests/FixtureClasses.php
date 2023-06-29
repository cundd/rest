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

ClassBuilder::buildClassIfNotExists(AbstractDomainObject::class);
ClassBuilder::buildClassIfNotExists(Repository::class);
ClassBuilder::buildClassIfNotExists(ObjectStorage::class, SplObjectStorage::class);
ClassBuilder::buildInterfaceIfNotExists(DomainObjectInterface::class);

/**
 * @method getUid()
 * @method _setProperty(string $name, $value)
 * @method array _getProperties()
 */
class BaseModel extends AbstractDomainObject implements DomainObjectInterface
{
    protected $uid;

    protected $pid;

    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function __wakeup()
    {
        // Prevent calling GeneralUtility::logDeprecatedFunction();
    }

    public function __call($name, $arguments)
    {
        $getUid = function () {
            return $this->uid;
        };

        /**
         * Reconstitutes a property. Only for internal use.
         *
         * @param string $propertyName
         * @param mixed  $propertyValue
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
                if ($propertyName[0] === '_') {
                    unset($properties[$propertyName]);
                }
            }

            return $properties;
        };

        if ($name === 'getUid') {
            return $getUid();
        }
        if ($name === '_setProperty') {
            return $_setProperty(...$arguments);
        }
        if ($name === '_getProperties') {
            return $_getProperties();
        }
        throw new BadMethodCallException();
    }
}

class MyModel extends BaseModel
{
    /**
     * @var int The uid of the record. The uid is only unique in the context of the database table.
     */
    protected $uid;

    /**
     * @var int The id of the page the record is "stored".
     */
    protected $pid;

    /**
     * @var string
     */
    protected $name = 'Initial value';

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}

class MyModelRepository extends Repository
{
}

class MyNestedModel extends BaseModel
{
    /**
     * @var string
     */
    protected $base = 'Base';

    /**
     * @var DateTime
     */
    protected $date = null;

    /**
     * @var MyModel
     */
    protected $child = null;

    public function __construct()
    {
        parent::__construct();
        $this->child = new MyModel();
        $this->date = new DateTime();
    }

    /**
     * @param string $base
     */
    public function setBase($base)
    {
        $this->base = $base;
    }

    /**
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @param MyModel|MyNestedModel $child
     */
    public function setChild($child)
    {
        $this->child = $child;
    }

    /**
     * @return MyModel|MyNestedModel
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}

class MyNestedModelWithObjectStorage extends MyNestedModel
{
    /**
     * @var ObjectStorage|array|Traversable
     */
    protected $children;

    /**
     * @return ObjectStorage|array|Traversable
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ObjectStorage|array|Traversable $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }
}

class MyNestedJsonSerializeModel extends MyNestedModel
{
    public function jsonSerialize()
    {
        return [
            'base'  => $this->base,
            'child' => $this->child,
        ];
    }
}

class SimpleClass
{
    public $firstName;

    public $lastName;

    protected $uid;

    protected $pid;

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
    function jsonSerialize(): array
    {
        return [
            "firstName" => $this->firstName,
            "lastName"  => $this->lastName,
            "uid"       => $this->uid,
            "pid"       => $this->pid,
        ];
    }
}
