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
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\Parser\ParseContext;
use MichaelHall\Webunit\Parser\Parser;
use MichaelHall\Webunit\RequestModifiers\WithHeader;
use MichaelHall\Webunit\RequestModifiers\WithPostFile;
use MichaelHall\Webunit\RequestModifiers\WithPostParameter;
use MichaelHall\Webunit\RequestModifiers\WithRawContent;
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
                'assert-contains \\sfoo',
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
        self::assertSame(' foo', $testCases[0]->getAsserts()[1]->getContent());
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

        self::assertSame(4, count($parseErrors));
        self::assertSame('foo.webunit:2: Duplicate modifier: Modifier "~" is duplicated for assert "assert-contains".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:3: Duplicate modifier: Modifier "!" is duplicated for assert "assert-contains".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:4: Invalid modifier: Modifier "~" is not allowed for assert "assert-empty".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:5: Invalid modifiers: Modifiers "^", "~" are not allowed for assert "assert-empty".', $parseErrors[3]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse with errors in regexp.
     */
    public function testParseWithRegexpErrors()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get https://example.com/',
                'assert-contains [Foo',
                'assert-contains~ [Bar',
            ],
            $parseContext,
        );

        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(2, count($testCases[0]->getAsserts()));

        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[1]);
        self::assertSame('[Foo', $testCases[0]->getAsserts()[1]->getContent());

        self::assertSame(1, count($parseErrors));
        self::assertSame('foo.webunit:3: Invalid regular expression: "[Bar" for assert "assert-contains~".', $parseErrors[0]->__toString());

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
        $parseContext->setVariable('command', 'GET');

        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get {{ Url }}',
                'assert-contains {{  }} {{}}',
                'assert-contains {{ Foo }}',
                'assert-contains {{F*o}}',
                'assert-contains {{F o}} {{ 1abc }}',
                '{{command}} {{Url}}',
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

        self::assertSame(5, count($parseErrors));
        self::assertSame('foo.webunit:2: Missing variable: Missing variable name in "{{  }}".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:3: Invalid variable: No variable with name "Foo" is set in "{{ Foo }}".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:4: Invalid variable: Invalid variable name "F*o" in "{{F*o}}".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:5: Invalid variable: Invalid variable name "F o" in "{{F o}}".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:6: Syntax error: Invalid command "{{command}}".', $parseErrors[4]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse with set commands and variables.
     *
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function testParseWithSetAndVariables()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseContext->setVariable('Host', 'example.com');
        $parseContext->setVariable('Path', '/foo/bar');
        $parseContext->setVariable('Content_2', 'BAR');

        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'set-default Host=example.org',
                'set Url=https://{{ Host }}{{ Path }}',
                "SET-DEFAULT Content_1 \t = \tFOO\\s \t",
                'set-default Content_1=Foo',
                'set Content_3 = ',
                'get {{ Url }}',
                'assert-contains {{ Content_1 }}',
                'assert-contains! {Content_1}}',
                'assert-contains {{ Content_1 }',
                'Set STATUS_CODE = 201',
                'assert-status-code {{STATUS_CODE}} ',
            ],
            $parseContext,
        );

        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));

        self::assertSame('https://example.com/foo/bar', $testCases[0]->getUrl()->__toString());
        self::assertSame(4, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[0]);
        self::assertSame('FOO ', $testCases[0]->getAsserts()[0]->getContent());
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[0]->getModifiers()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[1]);
        self::assertSame('{Content_1}}', $testCases[0]->getAsserts()[1]->getContent());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT))->equals($testCases[0]->getAsserts()[1]->getModifiers()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[2]);
        self::assertSame('{{ Content_1 }', $testCases[0]->getAsserts()[2]->getContent());
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[2]->getModifiers()));
        self::assertInstanceOf(AssertStatusCode::class, $testCases[0]->getAsserts()[3]);
        self::assertSame(201, $testCases[0]->getAsserts()[3]->getStatusCode());
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[3]->getModifiers()));

        self::assertSame(0, count($parseErrors));

        self::assertTrue($parseResult->isSuccess());

        self::assertSame('example.com', $parseContext->getVariable('Host'));
        self::assertSame('/foo/bar', $parseContext->getVariable('Path'));
        self::assertSame('https://example.com/foo/bar', $parseContext->getVariable('Url'));
        self::assertSame('FOO ', $parseContext->getVariable('Content_1'));
        self::assertSame('BAR', $parseContext->getVariable('Content_2'));
        self::assertSame('', $parseContext->getVariable('Content_3'));
        self::assertSame('201', $parseContext->getVariable('STATUS_CODE'));
    }

    /**
     * Test parse failure with set and variables.
     */
    public function testParseFailureWithSetAndVariables()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'set ',
                "  SET-DEFAULT\t",
                'SET=BAR',
                'SET-DEFAULT=BAR',
                'set  = Bar',
                "Set-Default \t=\t Bar",
                'SET Foo',
                ' SET-default Foo ',
                'set Foo: Bar',
                'set-default Foo:Bar',
                'set F*o = Bar',
                'set-default F#o = B*r',
                'set FOO=Foo',
                'set {{ FOO }}=Bar',
            ],
            $parseContext,
        );

        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(0, count($testCases));

        self::assertSame(13, count($parseErrors));
        self::assertSame('foo.webunit:1: Missing variable: Missing variable name and value for "set".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:2: Missing variable: Missing variable name and value for "set-default".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:3: Syntax error: Invalid command "set=bar".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:4: Syntax error: Invalid command "set-default=bar".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:5: Missing variable: Missing variable name for "set" in "= Bar".', $parseErrors[4]->__toString());
        self::assertSame("foo.webunit:6: Missing variable: Missing variable name for \"set-default\" in \"=\t Bar\".", $parseErrors[5]->__toString());
        self::assertSame('foo.webunit:7: Missing variable: Missing variable value for "set" in "Foo".', $parseErrors[6]->__toString());
        self::assertSame('foo.webunit:8: Missing variable: Missing variable value for "set-default" in "Foo".', $parseErrors[7]->__toString());
        self::assertSame('foo.webunit:9: Invalid variable: Invalid variable name "Foo: Bar" for "set" in "Foo: Bar".', $parseErrors[8]->__toString());
        self::assertSame('foo.webunit:10: Invalid variable: Invalid variable name "Foo:Bar" for "set-default" in "Foo:Bar".', $parseErrors[9]->__toString());
        self::assertSame('foo.webunit:11: Invalid variable: Invalid variable name "F*o" for "set" in "F*o = Bar".', $parseErrors[10]->__toString());
        self::assertSame('foo.webunit:12: Invalid variable: Invalid variable name "F#o" for "set-default" in "F#o = B*r".', $parseErrors[11]->__toString());
        self::assertSame('foo.webunit:14: Invalid variable: Invalid variable name "{{ FOO }}" for "set" in "{{ FOO }}=Bar".', $parseErrors[12]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse with other methods test.
     */
    public function testParseWithOtherMethodsTest()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'get https://example.com/',
                'post https://example.com/foo',
                '',
                '  PATCH   http://example.com/bar',
                'assert-contains Bar',
                'Put https://example.com/',
                ' delete https://example.com/',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertCount(5, $testCases);
        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_GET, $testCases[0]->getMethod());
        self::assertCount(1, $testCases[0]->getAsserts());
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame('https://example.com/foo', $testCases[1]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_POST, $testCases[1]->getMethod());
        self::assertCount(1, $testCases[1]->getAsserts());
        self::assertInstanceOf(DefaultAssert::class, $testCases[1]->getAsserts()[0]);
        self::assertSame('http://example.com/bar', $testCases[2]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_PATCH, $testCases[2]->getMethod());
        self::assertCount(2, $testCases[2]->getAsserts());
        self::assertInstanceOf(DefaultAssert::class, $testCases[2]->getAsserts()[0]);
        self::assertInstanceOf(AssertContains::class, $testCases[2]->getAsserts()[1]);
        self::assertSame('https://example.com/', $testCases[3]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_PUT, $testCases[3]->getMethod());
        self::assertCount(1, $testCases[3]->getAsserts());
        self::assertInstanceOf(DefaultAssert::class, $testCases[3]->getAsserts()[0]);
        self::assertSame('https://example.com/', $testCases[4]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_DELETE, $testCases[4]->getMethod());
        self::assertCount(1, $testCases[4]->getAsserts());
        self::assertInstanceOf(DefaultAssert::class, $testCases[4]->getAsserts()[0]);

        self::assertSame([], $parseResult->getParseErrors());
        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test parse with request modifiers.
     */
    public function testParseWithRequestModifiers()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse(__DIR__ . '/foo.webunit'),
            [
                'set FOO = Foo',
                'SET BAR = Bar',
                'set BAZ = Baz',
                'SET FILE = File',
                'SET HELLO_WORLD_TXT = helloworld.txt',
                'POST https://example.com/',
                'with-post-parameter {{ FOO }} = {{ BAR }}',
                'with-post-parameter Foo2=Bar2',
                " \tWITH-post-parameter  Name\t_2 =\tValue=2  \t",
                'with-post-parameter Empty=',
                'with-post-file {{ FILE }}=' . str_replace('\\', '\\\\', __DIR__) . '/../Helpers/TestFiles/{{HELLO_WORLD_TXT}}',
                " WITH-post-file File-2 \t=  \t../Helpers/TestFiles/helloworld.txt ",
                'with-header EmptyHeader:',
                '',
                'PUT https://example.com/',
                ' With-Raw-Content {"{{ FOO }}": "Bar"}',
                ' With-Raw-Content {"Foo2": "Bar2"}',
                "\tWITH-header   Bar-Header :\t{{ BAZ }}",
                "\tWITH-header   {{ BAR }}-Header2 :\t{{ BAZ }}",
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertCount(2, $testCases);

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_POST, $testCases[0]->getMethod());
        self::assertCount(7, $testCases[0]->getRequestModifiers());
        self::assertInstanceOf(WithPostParameter::class, $testCases[0]->getRequestModifiers()[0]);
        self::assertSame('Foo', $testCases[0]->getRequestModifiers()[0]->getParameterName());
        self::assertSame('Bar', $testCases[0]->getRequestModifiers()[0]->getParameterValue());
        self::assertInstanceOf(WithPostParameter::class, $testCases[0]->getRequestModifiers()[1]);
        self::assertSame('Foo2', $testCases[0]->getRequestModifiers()[1]->getParameterName());
        self::assertSame('Bar2', $testCases[0]->getRequestModifiers()[1]->getParameterValue());
        self::assertInstanceOf(WithPostParameter::class, $testCases[0]->getRequestModifiers()[2]);
        self::assertSame("Name\t_2", $testCases[0]->getRequestModifiers()[2]->getParameterName());
        self::assertSame('Value=2', $testCases[0]->getRequestModifiers()[2]->getParameterValue());
        self::assertInstanceOf(WithPostParameter::class, $testCases[0]->getRequestModifiers()[3]);
        self::assertSame('Empty', $testCases[0]->getRequestModifiers()[3]->getParameterName());
        self::assertSame('', $testCases[0]->getRequestModifiers()[3]->getParameterValue());
        self::assertInstanceOf(WithPostFile::class, $testCases[0]->getRequestModifiers()[4]);
        self::assertSame('File', $testCases[0]->getRequestModifiers()[4]->getParameterName());
        self::assertTrue(FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt')->equals($testCases[0]->getRequestModifiers()[4]->getFilePath()));
        self::assertInstanceOf(WithPostFile::class, $testCases[0]->getRequestModifiers()[5]);
        self::assertSame('File-2', $testCases[0]->getRequestModifiers()[5]->getParameterName());
        self::assertTrue(FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt')->equals($testCases[0]->getRequestModifiers()[5]->getFilePath()));
        self::assertInstanceOf(WithHeader::class, $testCases[0]->getRequestModifiers()[6]);
        self::assertSame('EmptyHeader', $testCases[0]->getRequestModifiers()[6]->getHeaderName());
        self::assertSame('', $testCases[0]->getRequestModifiers()[6]->getHeaderValue());

        self::assertSame('https://example.com/', $testCases[1]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_PUT, $testCases[1]->getMethod());
        self::assertCount(4, $testCases[1]->getRequestModifiers());
        self::assertInstanceOf(WithRawContent::class, $testCases[1]->getRequestModifiers()[0]);
        self::assertSame('{"Foo": "Bar"}', $testCases[1]->getRequestModifiers()[0]->getContent());
        self::assertInstanceOf(WithRawContent::class, $testCases[1]->getRequestModifiers()[1]);
        self::assertSame('{"Foo2": "Bar2"}', $testCases[1]->getRequestModifiers()[1]->getContent());
        self::assertInstanceOf(WithHeader::class, $testCases[1]->getRequestModifiers()[2]);
        self::assertSame('Bar-Header', $testCases[1]->getRequestModifiers()[2]->getHeaderName());
        self::assertSame('Baz', $testCases[1]->getRequestModifiers()[2]->getHeaderValue());
        self::assertInstanceOf(WithHeader::class, $testCases[1]->getRequestModifiers()[3]);
        self::assertSame('Bar-Header2', $testCases[1]->getRequestModifiers()[3]->getHeaderName());
        self::assertSame('Baz', $testCases[1]->getRequestModifiers()[3]->getHeaderValue());

        self::assertSame([], $parseResult->getParseErrors());
        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test parse failure with request modifiers.
     */
    public function testParseFailureWithRequestModifiers()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'with-post-parameter Foo=Bar',
                '',
                'put https://example.com/',
                'with-post-parameter',
                ' with-post-parameter Foo ',
                'With-post-parameter =',
                ' with-post-parameter = Bar ',
                'with-post-file',
                ' With-Post-File Foo',
                ' With-Post-File =',
                'with-post-file = Bar',
                "with-post-file File = F\0oo",
                'with-post-file File1=' . str_replace('\\', '\\\\', __DIR__) . '/../Helpers/TestFiles/not-found.txt',
                '',
                'GET https://example.com/',
                'with-post-parameter Foo=Bar',
                'with-post-file File1=' . str_replace('\\', '\\\\', __DIR__) . '/../Helpers/TestFiles/helloworld.txt',
                '',
                'POST https://example.com/',
                ' With-raw-content ',
                '',
                'get https://example.com/',
                'with-raw-content <Foo>Bar</Foo>',
                'With-Header',
                ' with-header Foo',
                'with-header :Bar',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(4, count($testCases));
        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame('https://example.com/', $testCases[1]->getUrl()->__toString());
        self::assertSame(1, count($testCases[1]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[1]->getAsserts()[0]);
        self::assertSame('https://example.com/', $testCases[2]->getUrl()->__toString());
        self::assertSame(1, count($testCases[2]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[2]->getAsserts()[0]);
        self::assertSame('https://example.com/', $testCases[3]->getUrl()->__toString());
        self::assertSame(1, count($testCases[3]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[3]->getAsserts()[0]);

        self::assertSame(18, count($parseErrors));
        self::assertSame('foo.webunit:1: Undefined test case: Test case is not defined for request modifier "with-post-parameter".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:4: Missing argument: Missing parameter name and value for request modifier "with-post-parameter".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:5: Missing argument: Missing parameter value for request modifier "with-post-parameter".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:6: Missing argument: Missing parameter name for request modifier "with-post-parameter".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:7: Missing argument: Missing parameter name for request modifier "with-post-parameter".', $parseErrors[4]->__toString());
        self::assertSame('foo.webunit:8: Missing argument: Missing parameter name and value for request modifier "with-post-file".', $parseErrors[5]->__toString());
        self::assertSame('foo.webunit:9: Missing argument: Missing parameter value for request modifier "with-post-file".', $parseErrors[6]->__toString());
        self::assertSame('foo.webunit:10: Missing argument: Missing parameter name for request modifier "with-post-file".', $parseErrors[7]->__toString());
        self::assertSame('foo.webunit:11: Missing argument: Missing parameter name for request modifier "with-post-file".', $parseErrors[8]->__toString());
        self::assertSame("foo.webunit:12: Invalid argument: File path \"F\0oo\" is not valid for request modifier \"with-post-file\".", $parseErrors[9]->__toString());
        self::assertSame('foo.webunit:13: Invalid argument: File "' . __DIR__ . '/../Helpers/TestFiles/not-found.txt" was not found for request modifier "with-post-file".', $parseErrors[10]->__toString());
        self::assertSame('foo.webunit:16: Invalid request modifier: Request modifier "with-post-parameter" is not allowed for request method "GET".', $parseErrors[11]->__toString());
        self::assertSame('foo.webunit:17: Invalid request modifier: Request modifier "with-post-file" is not allowed for request method "GET".', $parseErrors[12]->__toString());
        self::assertSame('foo.webunit:20: Missing argument: Missing content for request modifier "with-raw-content".', $parseErrors[13]->__toString());
        self::assertSame('foo.webunit:23: Invalid request modifier: Request modifier "with-raw-content" is not allowed for request method "GET".', $parseErrors[14]->__toString());
        self::assertSame('foo.webunit:24: Missing argument: Missing header name and value for request modifier "with-header".', $parseErrors[15]->__toString());
        self::assertSame('foo.webunit:25: Missing argument: Missing header value for request modifier "with-header".', $parseErrors[16]->__toString());
        self::assertSame('foo.webunit:26: Missing argument: Missing header name for request modifier "with-header".', $parseErrors[17]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse with incompatible request modifiers.
     */
    public function testParseWithIncompatibleRequestModifiers()
    {
        $parser = new Parser();
        $parseContext = new ParseContext();
        $parseResult = $parser->parse(
            FilePath::parse('foo.webunit'),
            [
                'POST https://example.com/',
                'with-raw-content {"Foo": "Bar"}',
                'with-post-parameter Foo=Bar',
                'with-post-file File1=' . str_replace('\\', '\\\\', __DIR__) . '/../Helpers/TestFiles/helloworld.txt',
                'with-post-parameter 1=2',
                'with-raw-content {"1": 2}',
                '',
                'PUT https://example.com/',
                'with-post-parameter Foo=Bar',
                'with-raw-content {"Foo": "Bar"}',
                '',
                'PUT https://example.com/',
                'with-post-file File1=' . str_replace('\\', '\\\\', __DIR__) . '/../Helpers/TestFiles/helloworld.txt',
                'with-raw-content {"Foo": "Bar"}',
            ],
            $parseContext,
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertCount(3, $testCases);

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_POST, $testCases[0]->getMethod());
        self::assertCount(2, $testCases[0]->getRequestModifiers());
        self::assertInstanceOf(WithRawContent::class, $testCases[0]->getRequestModifiers()[0]);
        self::assertSame('{"Foo": "Bar"}', $testCases[0]->getRequestModifiers()[0]->getContent());
        self::assertInstanceOf(WithRawContent::class, $testCases[0]->getRequestModifiers()[1]);
        self::assertSame('{"1": 2}', $testCases[0]->getRequestModifiers()[1]->getContent());

        self::assertSame('https://example.com/', $testCases[1]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_PUT, $testCases[1]->getMethod());
        self::assertCount(1, $testCases[1]->getRequestModifiers());
        self::assertInstanceOf(WithPostParameter::class, $testCases[1]->getRequestModifiers()[0]);
        self::assertSame('Foo', $testCases[1]->getRequestModifiers()[0]->getParameterName());
        self::assertSame('Bar', $testCases[1]->getRequestModifiers()[0]->getParameterValue());

        self::assertSame('https://example.com/', $testCases[2]->getUrl()->__toString());
        self::assertSame(TestCaseInterface::METHOD_PUT, $testCases[2]->getMethod());
        self::assertCount(1, $testCases[2]->getRequestModifiers());
        self::assertInstanceOf(WithPostFile::class, $testCases[2]->getRequestModifiers()[0]);
        self::assertSame('File1', $testCases[2]->getRequestModifiers()[0]->getParameterName());
        self::assertTrue(FilePath::parse(__DIR__ . '/../Helpers/TestFiles/helloworld.txt')->equals($testCases[2]->getRequestModifiers()[0]->getFilePath()));

        self::assertCount(5, $parseErrors);
        self::assertSame('foo.webunit:3: Incompatible request modifier: Request modifier "with-post-parameter" can not be combined with request modifier "with-raw-content".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:4: Incompatible request modifier: Request modifier "with-post-file" can not be combined with request modifier "with-raw-content".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:5: Incompatible request modifier: Request modifier "with-post-parameter" can not be combined with request modifier "with-raw-content".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:10: Incompatible request modifier: Request modifier "with-raw-content" can not be combined with request modifier "with-post-parameter".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:14: Incompatible request modifier: Request modifier "with-raw-content" can not be combined with request modifier "with-post-file".', $parseErrors[4]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }
}
