<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 05.08.15
 * Time: 22:03
 */

namespace Cundd\Rest\Test;


use Cundd\Rest\Request;

class RequestTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Request
     */
    protected $fixture;

    /**
     * @inheritDoc
     */
    protected function setUp() {
        parent::setUp();

        $uri = 'MyAliasedModel' . time();
        $path = strtok($uri, '/');
        $this->fixture = new \Cundd\Rest\Request(NULL, $uri);
        $this->fixture->initWithPathAndOriginalPath($path, $path);
    }

    /**
     * @test
     */
    public function getSentDataTest() {
        $testData = array(
            'myData' => array(
                'name' => 'Blur',
                'time' => time(),
            )
        );
        $_POST['myData'] = $testData['myData'];
        $this->assertSame($testData, $this->fixture->getSentData());
    }
}
