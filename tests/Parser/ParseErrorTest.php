<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use DataTypes\FilePath;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Parser\ParseError;
use PHPUnit\Framework\TestCase;

/**
 * Test ParseError class.
 */
class ParseErrorTest extends TestCase
{
    /**
     * Test constructor.
     */
    public function testConstructor()
    {
        $location = new FileLocation(FilePath::parse('/tmp/foo.webunit'), 12);
        $parseError = new ParseError($location, 'Syntax error');

        self::assertSame($location, $parseError->getLocation());
        self::assertSame('Syntax error', $parseError->getError());
        self::assertSame(DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'foo.webunit:12: Syntax error', $parseError->__toString());
    }
}
