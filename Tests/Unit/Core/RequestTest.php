<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 05.08.15
 * Time: 22:03
 */

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Tests\Unit\AbstractRequestBasedCase;

class RequestTest extends AbstractRequestBasedCase
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
            ),
        );
        $_POST['myData'] = $testData['myData'];
        $request = $this->buildTestRequest('MyAliasedModel' . time(), null);
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
            ),
        );
        $request = $this->buildTestRequest('MyAliasedModel' . time(), null, array(), array(), json_encode($testData));
        $this->assertSame($testData, $request->getSentData());
    }


}
