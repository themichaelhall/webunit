<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use DataTypes\FilePath;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\DefaultAssert;
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
                'get http://example.com/',
                'assert-empty',
                'assert-empty foo',
            ]
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();
        $parseErrors = $parseResult->getParseErrors();

        self::assertSame(1, count($testCases));
        self::assertSame('http://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(2, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertInstanceOf(AssertEmpty::class, $testCases[0]->getAsserts()[1]);
        self::assertSame(1, count($parseErrors));
        self::assertSame('foo.webunit:3: Extra argument: "foo". No arguments are allowed for assert "assert-empty".', $parseErrors[0]->__toString());
        self::assertFalse($parseResult->isSuccess());
    }
}
