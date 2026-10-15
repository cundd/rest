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
    /**
     * @param non-empty-string $input
     */
    #[Test]
    #[DataProvider('validFormatDataProvider')]
    public function validFormatTest(string $input): void
    {
        $this->assertEquals($input, (string) new Format($input));
    }

    /**
     * @return list<array{0:non-empty-string}>
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
    public function invalidFormatTest(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Format($input); // @phpstan-ignore argument.type
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
