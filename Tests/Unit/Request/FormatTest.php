<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Request;

use Cundd\Rest\Request\Format;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    #[Test]
    #[DataProvider('validFormatDataProvider')]
    public function validFormatTest(mixed $input): void
    {
        $this->assertEquals($input, (string) new Format($input));
    }

    /**
     * @return list<array{0:string}>
     */
    public static function validFormatDataProvider(): array
    {
        return [
            ['json'],
            ['html'],
            ['xml'],
        ];
    }

    #[Test]
    #[DataProvider('invalidFormatDataProvider')]
    public function invalidFormatTest(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Format($input);
    }

    /**
     * @return list<list<string>>
     */
    public static function invalidFormatDataProvider(): array
    {
        return [
            ['blur'],
            [''],
        ];
    }
}
