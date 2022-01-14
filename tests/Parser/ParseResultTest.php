<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use DataTypes\System\FilePath;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Parser\ParseError;
use MichaelHall\Webunit\Parser\ParseResult;
use MichaelHall\Webunit\TestSuite;
use PHPUnit\Framework\TestCase;

/**
 * Test ParseResult class.
 */
class ParseResultTest extends TestCase
{
    /**
     * Test successful result.
     */
    public function testSuccessfulResult()
    {
        $testSuite = new TestSuite();
        $parseResult = new ParseResult($testSuite, []);

        self::assertSame($testSuite, $parseResult->getTestSuite());
        self::assertSame([], $parseResult->getParseErrors());
        self::assertTrue($parseResult->isSuccess());
    }

    /**
     * Test unsuccessful result.
     */
    public function testUnsuccessfulResult()
    {
        $testSuite = new TestSuite();
        $parseErrors = [new ParseError(new FileLocation(FilePath::parse('/foo/bar'), 10), 'Failed')];
        $parseResult = new ParseResult($testSuite, $parseErrors);

        self::assertSame($testSuite, $parseResult->getTestSuite());
        self::assertSame($parseErrors, $parseResult->getParseErrors());
        self::assertFalse($parseResult->isSuccess());
    }
}
