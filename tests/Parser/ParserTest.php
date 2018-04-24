<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

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
        $parseResult = $parser->parse([
                'get http://example.com/',
            ]
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertSame(1, count($testSuite->getTestCases()));
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
        $parseResult = $parser->parse([
                '',
                "\t \r\n",
                "  get \thttp://example.com/ \t",
                ' ',
            ]
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertSame(1, count($testSuite->getTestCases()));
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
        $parseResult = $parser->parse([
                '# This is a comment',
                '#',
                'get http://example.com/',
            ]
        );
        $testSuite = $parseResult->getTestSuite();
        $testCases = $testSuite->getTestCases();

        self::assertSame(1, count($testSuite->getTestCases()));
        self::assertSame('http://example.com/', $testCases[0]->getUrl()->__toString());
        self::assertSame(1, count($testCases[0]->getAsserts()));
        self::assertInstanceOf(DefaultAssert::class, $testCases[0]->getAsserts()[0]);
        self::assertSame([], $parseResult->getParseErrors());
        self::assertTrue($parseResult->isSuccess());
    }
}
