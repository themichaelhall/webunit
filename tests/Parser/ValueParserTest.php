<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use MichaelHall\Webunit\Exceptions\ValueParserException;
use MichaelHall\Webunit\Parser\ValueParser;
use PHPUnit\Framework\TestCase;

/**
 * Test ValueParser class.
 */
class ValueParserTest extends TestCase
{
    /**
     * Test parsing plain text into a value.
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
            [' \\s \\r\\nFoo \\t\\sBar\\\\Baz \\t ', "  \r\nFoo \t Bar\\Baz \t"],
        ];
    }

    /**
     * Test parsing text with escape character errors.
     *
     * @dataProvider parseTextWithEscapeCharacterErrorsDataProvider
     */
    public function testParseTextWithEscapeCharacterErrors(string $text, string $expectedExceptionMessage)
    {
        self::expectException(ValueParserException::class);
        self::expectExceptionMessage($expectedExceptionMessage);

        $valueParser = new ValueParser();
        $valueParser->parseText($text);
    }

    /**
     * Data provider for testParseTextWithEscapeCharacterErrors method.
     *
     * @return array
     */
    public function parseTextWithEscapeCharacterErrorsDataProvider(): array
    {
        return [
            ['\\1', 'Invalid escape sequence "\\1" in "\\1".'],
            ['\\', 'Unterminated escape sequence in "\".'],
            [' \\1 ', 'Invalid escape sequence "\\1" in "\\1".'],
            [' \\ ', 'Unterminated escape sequence in "\".'],
            ['Foo \\Bar', 'Invalid escape sequence "\\B" in "Foo \\Bar".'],
            ['Foo Bar\\', 'Unterminated escape sequence in "Foo Bar\\".'],
        ];
    }
}
