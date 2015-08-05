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
        $request = new \Cundd\Rest\Request(NULL, $uri);
        $request->initWithPathAndOriginalPath($path, $path);
    }

    /**
     * @test
     */
    public function getSentDataTest() {
        $testData = array(
            'name' => 'Blur',
            'time' => time()
        );
        $_POST['myData'] = $testData;
        $this->assertSame($testData, $this->fixture->getSentData());
    }
}
