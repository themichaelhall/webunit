<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use DataTypes\System\FilePath;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Assertions\AssertHeader;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Assertions\DefaultAssert;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\Parser\ParseContext;
use MichaelHall\Webunit\Parser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Test Parser class.
 */
class ParserTest extends TestCase
{
    /**
     * Test parse with empty test.
     */
    public function testParseWithEmptyTest()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get http://example.com/',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertSame(1, count($testCases));
        self::assertSame('http://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame([], $parseResult->getParseErrors());
        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test parse with whitespaces.
     */
    public function testParseWithWhitespaces()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                '',
                "\t \r\n",
                "  get \thttp://example.com/ \t",
                ' ',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertSame(1, count($testCases));
        self::assertSame('http://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame([], $parseResult->getParseErrors());
        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test parse with whitespaces.
     */
    public function testParseWithComments()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                '# This is a comment',
                '#',
                'get http://example.com/',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertSame(1, count($testCases));
        self::assertSame('http://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame([], $parseResult->getParseErrors());
        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test parse invalid command.
     */
    public function testParseInvalidCommand()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                '#',
                'foo bar',
                'get http://example.com/',
                'get',
                'get FooBar',
                'baz',
                '^',
                'get!',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));
        self::assertSame('http://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame(6, count($parseErrors));
        self::assertSame('foo.webunit:2: Syntax error: Invalid command "foo".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:4: Missing argument: Missing Url argument for "get".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:5: Invalid argument: Invalid Url argument "FooBar" for "get": Url "FooBar" is invalid: Scheme is missing.', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:6: Syntax error: Invalid command "baz".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:7: Syntax error: Invalid command "^".', $parseErrors[4]->__toString());
        self::assertSame('foo.webunit:8: Syntax error: Invalid command "get!".', $parseErrors[5]->__toString());
        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse with asserts.
     *
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function testParseWithAsserts()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'assert-contains foo',
                '',
                'get https://example.com/',
                'assert-contains',
                'assert-contains foo',
                'assert-empty',
                'assert-empty bar',
                '',
                'get https://example.com/foo',
                'assert-equals',
                'assert-equals baz',
                'assert-status-code',
                'assert-status-code foo',
                'assert-status-code 600',
                'assert-status-code 401',
                'assert-header',
                'assert-header Location',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(2, count($testCases));

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(3, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[1]);
        self::assertSame('foo', $testCases[0]->getAsserts()[1]->getContent());
        self::assertInstanceOf(AssertEmpty::class, $testCases[0]->getAsserts()[2]);

        self::assertSame('https://example.com/foo', $testCases[1]->getUrl()->__toString());
        self::assertSame(3, count($testCases[1]->getAsserts()));
        self::assertInstanceOf(AssertEquals::class, $testCases[1]->getAsserts()[0]);
        self::assertSame('baz', $testCases[1]->getAsserts()[0]->getContent());
        self::assertInstanceOf(AssertStatusCode::class, $testCases[1]->getAsserts()[1]);
        self::assertSame(401, $testCases[1]->getAsserts()[1]->getStatusCode());
        self::assertInstanceOf(AssertHeader::class, $testCases[1]->getAsserts()[2]);
        self::assertSame('Location', $testCases[1]->getAsserts()[2]->getHeaderName());
        self::assertNull($testCases[1]->getAsserts()[2]->getHeaderValue());

        self::assertSame(8, count($parseErrors));
        self::assertSame('foo.webunit:1: Undefined test case: Test case is not defined for assert "assert-contains".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:4: Missing argument: Missing content argument for assert "assert-contains".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:7: Extra argument: "bar". No arguments are allowed for assert "assert-empty".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:10: Missing argument: Missing content argument for assert "assert-equals".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:12: Missing argument: Missing status code argument for assert "assert-status-code".', $parseErrors[4]->__toString());
        self::assertSame('foo.webunit:13: Invalid argument: Status code "foo" must be of type integer for assert "assert-status-code".', $parseErrors[5]->__toString());
        self::assertSame('foo.webunit:14: Invalid argument: Status code 600 must be in range 100-599 for assert "assert-status-code".', $parseErrors[6]->__toString());
        self::assertSame('foo.webunit:16: Missing argument: Missing header argument for assert "assert-header".', $parseErrors[7]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse asserts with modifiers.
     *
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function testParseAssertsWithModifiers()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get https://example.com/',
                'assert-empty',
                'assert-empty!',
                'assert-contains~^ Foo',
                'assert-header!~^ Content-type: text/html',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(5, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertInstanceOf(AssertEmpty::class, $testCases[0]->getAsserts()[1]);
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[1]->getModifiers()));
        self::assertInstanceOf(AssertEmpty::class, $testCases[0]->getAsserts()[2]);
        self::assertTrue((new Modifiers(ModifiersInterface::NOT))->equals($testCases[0]->getAsserts()[2]->getModifiers()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[3]);
        self::assertSame('Foo', $testCases[0]->getAsserts()[3]->getContent());
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->equals($testCases[0]->getAsserts()[3]->getModifiers()));
        self::assertInstanceOf(AssertHeader::class, $testCases[0]->getAsserts()[4]);
        self::assertSame('Content-type', $testCases[0]->getAsserts()[4]->getHeaderName());
        self::assertSame('text/html', $testCases[0]->getAsserts()[4]->getHeaderValue());
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP | ModifiersInterface::NOT))->equals($testCases[0]->getAsserts()[4]->getModifiers()));

        self::assertSame(0, count($parseErrors));

        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test parse with errors in modifiers.
     */
    public function testParseWithModifiersErrors()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get https://example.com/',
                'assert-contains~!~ Foo',
                'assert-contains!!^^ Bar',
                'assert-empty~',
                'assert-empty~^',
            ],
            $parseContext,
        );

        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);

        self::assertSame(5, count($parseErrors));
        self::assertSame('foo.webunit:2: Duplicate modifier: Modifier "~" is duplicated for assert "assert-contains".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:3: Duplicate modifier: Modifier "!" is duplicated for assert "assert-contains".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:3: Duplicate modifier: Modifier "^" is duplicated for assert "assert-contains".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:4: Invalid modifier: Modifier "~" is not allowed for assert "assert-empty".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:5: Invalid modifiers: Modifiers "^", "~" are not allowed for assert "assert-empty".', $parseErrors[4]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse with variables.
     *
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function testParseWithVariables()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseContext->setVariable('Url', 'https://example.com/foo/bar');
        $parseContext->setVariable('Content_1', 'FOO');
        $parseContext->setVariable('Content_2', 'BAR');
        $parseContext->setVariable('Content_3', '');
        $parseContext->setVariable('headerName', 'X-Test-Header');
        $parseContext->setVariable('headerValue', '12345');
        $parseContext->setVariable('STATUS_CODE', '201');

        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get {{ Url }}',
                'assert-contains {{ Content_1 }}',
                'assert-contains! {Content_1}}',
                'assert-contains {{ Content_1 }',
                "assert-equals~^ {{Content_1}}{{ \t Content_2 \t}}{{ Content_3 }}",
                'assert-header {{ headerName }}:{{headerValue}}',
                'assert-status-code {{STATUS_CODE}} ',
            ],
            $parseContext,
        );

        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));

        self::assertSame('https://example.com/foo/bar', $testCases[0]->getUrl()->__toString());
        self::assertSame(6, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[0]);
        self::assertSame('FOO', $testCases[0]->getAsserts()[0]->getContent());
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[0]->getModifiers()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[1]);
        self::assertSame('{Content_1}}', $testCases[0]->getAsserts()[1]->getContent());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT))->equals($testCases[0]->getAsserts()[1]->getModifiers()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[2]);
        self::assertSame('{{ Content_1 }', $testCases[0]->getAsserts()[2]->getContent());
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[2]->getModifiers()));
        self::assertInstanceOf(AssertEquals::class, $testCases[0]->getAsserts()[3]);
        self::assertSame('FOOBAR', $testCases[0]->getAsserts()[3]->getContent());
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->equals($testCases[0]->getAsserts()[3]->getModifiers()));
        self::assertInstanceOf(AssertHeader::class, $testCases[0]->getAsserts()[4]);
        self::assertSame('X-Test-Header', $testCases[0]->getAsserts()[4]->getHeaderName());
        self::assertSame('12345', $testCases[0]->getAsserts()[4]->getHeaderValue());
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[4]->getModifiers()));
        self::assertInstanceOf(AssertStatusCode::class, $testCases[0]->getAsserts()[5]);
        self::assertSame(201, $testCases[0]->getAsserts()[5]->getStatusCode());
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[5]->getModifiers()));

        self::assertSame(0, count($parseErrors));

        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test parse failure with variables.
     */
    public function testParseFailureWithVariables()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseContext->setVariable('Url', 'https://example.com/foo/bar');
        $parseContext->setVariable('FOO', 'BAR');

        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get {{ Url }}',
                'assert-contains {{  }} {{}}',
                'assert-contains {{ Foo }}',
                'assert-contains {{F*o}}',
                'assert-contains {{F o}} {{ 1abc }}',
            ],
            $parseContext,
        );

        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));

        self::assertSame('https://example.com/foo/bar', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);

        self::assertSame(6, count($parseErrors));
        self::assertSame('foo.webunit:2: Missing variable: Missing variable name in "{{  }}".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:2: Missing variable: Missing variable name in "{{}}".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:3: Invalid variable: No variable with name "Foo" is set in "{{ Foo }}".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:4: Invalid variable: Invalid variable name "F*o" in "{{F*o}}".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:5: Invalid variable: Invalid variable name "F o" in "{{F o}}".', $parseErrors[4]->__toString());
        self::assertSame('foo.webunit:5: Invalid variable: Invalid variable name "1abc" in "{{ 1abc }}".', $parseErrors[5]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }
}
