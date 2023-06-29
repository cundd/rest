<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Domain\Model;

use Cundd\Rest\Domain\Model\Format;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class FormatTest extends TestCase
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

    public function validFormatDataProvider(): array
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
     */
    public function invalidFormatTest($input)
    {
        $this->expectException(InvalidArgumentException::class);
        new Format($input);
    }

    public function invalidFormatDataProvider(): array
    {
        return [
            ['blur',],
            ['',],
            [null],
            [new stdClass()],
            [[]],
        ];
    }
}
