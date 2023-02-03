<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use MichaelHall\Webunit\Exceptions\ParseException;
use MichaelHall\Webunit\Exceptions\ValueParserException;
use MichaelHall\Webunit\Parser\ParseContext;
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
        $valueParser = new ValueParser(new ParseContext());
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

        $valueParser = new ValueParser(new ParseContext());
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

    /**
     * Test parsing text with variables.
     */
    public function testParseTextWithVariables()
    {
        $parseContext = new ParseContext();
        $parseContext->setVariable('Foo', 'Bar');
        $parseContext->setVariable('Foo_2', '\\n Bar2 ');
        $parseContext->setVariable('FOO', 'Baz');

        $valueParser = new ValueParser($parseContext);
        $value = $valueParser->parseText(" {{ Foo }} \t{Foo} {{\tFoo_2}}{{FOO}} Foo-Bar");

        self::assertSame("Bar \t{Foo} \\n Bar2 Baz Foo-Bar", $value);
    }

    /**
     * Test parsing text with variables errors.
     *
     * @dataProvider parseTextWithVariableErrorsDataProvider
     */
    public function testParseTextWithVariableErrors(string $text, string $expectedExceptionMessage)
    {
        self::expectException(ParseException::class);
        self::expectExceptionMessage($expectedExceptionMessage);

        $parseContext = new ParseContext();
        $parseContext->setVariable('Foo', 'Bar');

        $valueParser = new ValueParser($parseContext);
        $valueParser->parseText($text);
    }

    /**
     * Data provider for testParseTextWithVariableErrors method.
     *
     * @return array
     */
    public function parseTextWithVariableErrorsDataProvider(): array
    {
        return [
            ['{{}}', 'Missing variable: Missing variable name in "{{}}".'],
            ['{{ }}', 'Missing variable: Missing variable name in "{{ }}".'],
            ['{{ F*o }}', 'Invalid variable: Invalid variable name "F*o" in "{{ F*o }}".'],
            ['{{ F o }} Bar', 'Invalid variable: Invalid variable name "F o" in "{{ F o }}".'],
            ['{{ FOO }}', 'Invalid variable: No variable with name "FOO" is set in "{{ FOO }}".'],
            ['{{ Bar }}', 'Invalid variable: No variable with name "Bar" is set in "{{ Bar }}".'],
        ];
    }

    /**
     * Test isValidVariableName method.
     */
    public function testIsValidVariableName()
    {
        self::assertFalse(ValueParser::isValidVariableName(''));
        self::assertFalse(ValueParser::isValidVariableName(' '));
        self::assertFalse(ValueParser::isValidVariableName(' A '));
        self::assertFalse(ValueParser::isValidVariableName('123A'));
        self::assertFalse(ValueParser::isValidVariableName('ABC-123'));
        self::assertTrue(ValueParser::isValidVariableName('A'));
        self::assertTrue(ValueParser::isValidVariableName('A1'));
        self::assertTrue(ValueParser::isValidVariableName('_A'));
        self::assertTrue(ValueParser::isValidVariableName('abc_123'));
        self::assertTrue(ValueParser::isValidVariableName('__Foo_Bar_123__'));
    }
}
