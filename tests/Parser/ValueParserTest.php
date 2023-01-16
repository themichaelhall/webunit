<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use MichaelHall\Webunit\Parser\ValueParser;
use PHPUnit\Framework\TestCase;

/**
 * Test ValueParser class.
 */
class ValueParserTest extends TestCase
{
    /**
     * Test paring plain text into a value.
     *
     * @dataProvider parsePlainTextDataProvider
     */
    public function testParsePlainText(string $text, string $expectedValue)
    {
        $valueParser = new ValueParser();
        $value = $valueParser->parseText($text);

        self::assertSame($expectedValue, $value);
    }

    /**
     * Data provider for testParsePlainText method.
     *
     * @return array
     */
    public function parsePlainTextDataProvider(): array
    {
        return [
            ['', ''],
            ['Foo', 'Foo'],
            ['  Bar  ', 'Bar'],
            [" \tFoo  \nBar  ", "Foo  \nBar"],
            ['Foo "Bar" ', 'Foo "Bar"'],
            [" Foo 'Bar\" Baz' \t", 'Foo \'Bar" Baz\''],
        ];
    }
}
