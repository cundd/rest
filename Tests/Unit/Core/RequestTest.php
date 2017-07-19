<?php

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Tests\Unit\AbstractRequestBasedCase;

class RequestTest extends AbstractRequestBasedCase
{
    /**
     * @test
     */
    public function getSentDataTest()
    {
        $testData = [
            'myData' => [
                'name' => 'Blur',
                'time' => time(),
            ],
        ];
        $_POST['myData'] = $testData['myData'];
        $request = $this->buildTestRequest('MyAliasedModel' . time(), null);
        $this->assertSame($testData, $request->getSentData());
    }

    /**
     * @test
     */
    public function getSentDataFromRawBodyTest()
    {
        $testData = [
            'myData' => [
                'name' => 'Test Name',
                'time' => time(),
            ],
        ];
        $request = $this->buildTestRequest('MyAliasedModel' . time(), null, [], [], json_encode($testData));
        $this->assertSame($testData, $request->getSentData());
    }


}
