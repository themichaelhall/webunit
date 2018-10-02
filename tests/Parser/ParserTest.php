<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use DataTypes\FilePath;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Assertions\DefaultAssert;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Modifiers;
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
        $parseResult = $parser->parse(FilePath::parse('foo.webunit'),
            [
                'get http://example.com/',
            ]
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
        $parseResult = $parser->parse(FilePath::parse('foo.webunit'),
            [
                '',
                "\t \r\n",
                "  get \thttp://example.com/ \t",
                ' ',
            ]
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
        $parseResult = $parser->parse(FilePath::parse('foo.webunit'),
            [
                '# This is a comment',
                '#',
                'get http://example.com/',
            ]
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
        $parseResult = $parser->parse(FilePath::parse('foo.webunit'),
            [
                '#',
                'foo bar',
                'get http://example.com/',
                'get',
                'get FooBar',
                'baz',
            ]
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));
        self::assertSame('http://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame(4, count($parseErrors));
        self::assertSame('foo.webunit:2: Syntax error: Invalid command "foo".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:4: Missing argument: Missing Url argument for "get".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:5: Invalid argument: Invalid Url argument "FooBar" for "get": Url "FooBar" is invalid: Scheme is missing.', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:6: Syntax error: Invalid command "baz".', $parseErrors[3]->__toString());
        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse with asserts.
     */
    public function testParseWithAsserts()
    {
        $parser = new Parser();
        $parseResult = $parser->parse(FilePath::parse('foo.webunit'),
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
            ]
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(2, count($testCases));

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(3, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[1]);
        self::assertInstanceOf(AssertEmpty::class, $testCases[0]->getAsserts()[2]);

        self::assertSame('https://example.com/foo', $testCases[1]->getUrl()->__toString());
        self::assertSame(2, count($testCases[1]->getAsserts()));
        self::assertInstanceOf(AssertEquals::class, $testCases[1]->getAsserts()[0]);
        self::assertInstanceOf(AssertStatusCode::class, $testCases[1]->getAsserts()[1]);

        self::assertSame(7, count($parseErrors));
        self::assertSame('foo.webunit:1: Undefined test case: Test case is not defined for assert "assert-contains".', $parseErrors[0]->__toString());
        self::assertSame('foo.webunit:4: Missing argument: Missing content argument for assert "assert-contains".', $parseErrors[1]->__toString());
        self::assertSame('foo.webunit:7: Extra argument: "bar". No arguments are allowed for assert "assert-empty".', $parseErrors[2]->__toString());
        self::assertSame('foo.webunit:10: Missing argument: Missing content argument for assert "assert-equals".', $parseErrors[3]->__toString());
        self::assertSame('foo.webunit:12: Missing argument: Missing status code argument for assert "assert-status-code".', $parseErrors[4]->__toString());
        self::assertSame('foo.webunit:13: Invalid argument: Status code "foo" must be of type integer for assert "assert-status-code".', $parseErrors[5]->__toString());
        self::assertSame('foo.webunit:14: Invalid argument: Status code 600 must be in range 100-599 for assert "assert-status-code".', $parseErrors[6]->__toString());

        self::assertFalse($parseResult->isSuccess());
    }

    /**
     * Test parse asserts with modifiers.
     */
    public function testParseAssertsWithModifiers()
    {
        $parser = new Parser();
        $parseResult = $parser->parse(FilePath::parse('foo.webunit'),
            [
                'get https://example.com/',
                'assert-empty',
                'assert-empty!',
                'assert-contains~^ Foo',
            ]
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));

        self::assertSame('https://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(4, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertInstanceOf(AssertEmpty::class, $testCases[0]->getAsserts()[1]);
        self::assertTrue((new Modifiers())->equals($testCases[0]->getAsserts()[1]->getModifiers()));
        self::assertInstanceOf(AssertEmpty::class, $testCases[0]->getAsserts()[2]);
        self::assertTrue((new Modifiers(ModifiersInterface::NOT))->equals($testCases[0]->getAsserts()[2]->getModifiers()));
        self::assertInstanceOf(AssertContains::class, $testCases[0]->getAsserts()[3]);
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->equals($testCases[0]->getAsserts()[3]->getModifiers()));

        self::assertSame(0, count($parseErrors));

        self::assertTrue($parseResult->isSuccess());
    }
}
