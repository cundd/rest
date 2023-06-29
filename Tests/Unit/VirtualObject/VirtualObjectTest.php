<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\VirtualObject;

use Cundd\Rest\VirtualObject\VirtualObject;
use PHPUnit\Framework\TestCase;

/**
 * Virtual Object tests
 */
class VirtualObjectTest extends TestCase
{
    /**
     * @var VirtualObject
     */
    protected $fixture;

    public function setUp(): void
    {
        $this->fixture = new VirtualObject(
            [
                'firstName' => 'Daniel',
                'lastName'  => 'Corn',
                'age'       => 27,
            ]
        );
    }

    public function tearDown(): void
    {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function getTest()
    {
        $this->assertEquals('Daniel', $this->fixture->valueForKey('firstName'));
        $this->assertEquals('Corn', $this->fixture->valueForKey('lastName'));
    }

    /**
     * @test
     */
    public function setTest()
    {
        $this->fixture->setValueForKey('firstName', 'Steve');
        $this->fixture->setValueForKey('lastName', 'Jobs');

        $this->assertEquals('Steve', $this->fixture->valueForKey('firstName'));
        $this->assertEquals('Jobs', $this->fixture->valueForKey('lastName'));
    }
}
