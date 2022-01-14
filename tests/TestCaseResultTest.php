<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\Net\Url;
use DataTypes\System\FilePath;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\AssertResult;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\TestCaseResult;
use PHPUnit\Framework\TestCase;

/**
 * Test TestCaseResult class.
 */
class TestCaseResultTest extends TestCase
{
    /**
     * Test successful result.
     */
    public function testSuccessfulResult()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));
        $testCaseResult = new TestCaseResult($testCase);

        self::assertSame($testCase, $testCaseResult->getTestCase());
        self::assertTrue($testCaseResult->isSuccess());
        self::assertNull($testCaseResult->getFailedAssertResult());
    }

    /**
     * Test unsuccessful result.
     */
    public function testUnsuccessfulResult()
    {
        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost'));
        $assert = new AssertContains($location, 'Foo', new Modifiers());
        $assertResult = new AssertResult($assert, false, 'Fail');
        $testCaseResult = new TestCaseResult($testCase, $assertResult);

        self::assertSame($testCase, $testCaseResult->getTestCase());
        self::assertFalse($testCaseResult->isSuccess());
        self::assertSame($assertResult, $testCaseResult->getFailedAssertResult());
    }
}
