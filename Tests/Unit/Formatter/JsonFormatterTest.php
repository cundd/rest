<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02/06/16
 * Time: 19:34
 */

namespace Cundd\Rest\Tests\Unit;

use Cundd\Rest\Formatter\JsonFormatter;

require_once __DIR__ . '/../../Bootstrap.php';

/**
 * JSON Formatter
 */
class JsonFormatterTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var JsonFormatter
     */
    private $fixture;

    protected function setUp() {
        $this->fixture = new \Cundd\Rest\Formatter\JsonFormatter();
    }

    protected function tearDown() {
        unset($this->fixture);
    }

    public function formatDataProvider() {
        $testArray = array(
            'firstName' => 'Daniel',
            'lastName'  => 'Corn',
            'country'   => 'Austria',
            'age'       => 29,
        );
        $testArrayWithHobbies = $testArray;
        $testArrayWithHobbies['hobbies'] = array(
            'Playing guitar',
            'Programming',
        );
        $testObject = (object)$testArray;

        return array(
            array(null, 'null'),
            array(array(), '[]'),
            array(new \stdClass(), '{}'),
            array($testArray, '{"firstName":"Daniel","lastName":"Corn","country":"Austria","age":29}'),
            array(array($testArray), '[{"firstName":"Daniel","lastName":"Corn","country":"Austria","age":29}]'),
            array($testArrayWithHobbies, '{"firstName":"Daniel","lastName":"Corn","country":"Austria","age":29,"hobbies":["Playing guitar","Programming"]}'),
            array($testObject, '{"firstName":"Daniel","lastName":"Corn","country":"Austria","age":29}'),
        );
    }


    /**
     * @test
     * @dataProvider formatDataProvider
     * @param $input
     * @param $expected
     */
    public function formatTest($input, $expected) {
        $this->assertEquals($expected, $this->fixture->format($input));
    }
}
