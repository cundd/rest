<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 05.08.15
 * Time: 22:03
 */

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Request;

require_once __DIR__ . '/../../Bootstrap.php';

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getSentDataTest()
    {
        $testData = array(
            'myData' => array(
                'name' => 'Blur',
                'time' => time(),
            )
        );
        $_POST['myData'] = $testData['myData'];
        $request = $this->buildTestRequest(null, 'MyAliasedModel' . time());
        $this->assertSame($testData, $request->getSentData());
    }

    /**
     * @test
     */
    public function getSentDataFromRawBodyTest()
    {
        $testData = array(
            'myData' => array(
                'name' => 'Test Name',
                'time' => time(),
            )
        );
        $request = $this->buildTestRequest(null, 'MyAliasedModel' . time(), array(), array(), json_encode($testData));
        $this->assertSame($testData, $request->getSentData());
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param mixed $rawBody
     * @return Request
     */
    protected function buildTestRequest($method = null, $url = null, array $params = array(), array $headers = array(), $rawBody = null)
    {
        $path = strtok($url, '/');
        $request = new \Cundd\Rest\Request($method, $url, $params, $headers, $rawBody);
        $request->initWithPathAndOriginalPath($path, $path);
        return $request;
    }
}
