<?php
declare(strict_types=1);


namespace Cundd\Rest\Tests\Unit\VirtualObject;

use Cundd\Rest\VirtualObject\VirtualObject;

/**
 * Virtual Object tests
 */
class VirtualObjectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Cundd\Rest\VirtualObject\VirtualObject
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = new VirtualObject(
            [
                'firstName' => 'Daniel',
                'lastName'  => 'Corn',
                'age'       => 27,
            ]
        );
    }

    public function tearDown()
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
