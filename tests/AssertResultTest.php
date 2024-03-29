<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\System\FilePath;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\AssertResult;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use PHPUnit\Framework\TestCase;

/**
 * Test TestResult class.
 */
class AssertResultTest extends TestCase
{
    /**
     * Test a successful result.
     */
    public function testSuccessfulResult()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert = new AssertContains($location, 'Foo', new Modifiers());
        $result = new AssertResult($assert);

        self::assertTrue($result->isSuccess());
        self::assertSame('', $result->getError());
        self::assertSame($assert, $result->getAssert());
    }

    /**
     * Test an unsuccessful result.
     */
    public function testUnsuccessfulResult()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $assert = new AssertContains($location, 'Foo', new Modifiers());
        $result = new AssertResult($assert, false, 'Bar');

        self::assertFalse($result->isSuccess());
        self::assertSame('Bar', $result->getError());
        self::assertSame($assert, $result->getAssert());
    }
}
