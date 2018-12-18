<?php
declare(strict_types=1);

namespace Cundd\Rest\Domain\Model;


class FormatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @dataProvider validFormatDataProvider
     * @param $input
     */
    public function validFormatTest($input)
    {
        $this->assertEquals($input, (string)new Format($input));
    }

    public function validFormatDataProvider()
    {
        return [
            ['json',],
            ['html',],
            ['xml',],
        ];
    }

    /**
     * @test
     * @dataProvider invalidFormatDataProvider
     * @param $input
     * @expectedException \InvalidArgumentException
     */
    public function invalidFormatTest($input)
    {
        new Format($input);
    }

    public function invalidFormatDataProvider()
    {
        return [
            ['blur',],
            ['',],
            [null],
            [new \stdClass()],
            [[]],
        ];
    }
}
