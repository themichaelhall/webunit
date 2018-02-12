<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\AssertResult;
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
        $testCase = new \MichaelHall\Webunit\TestCase();
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
        $testCase = new \MichaelHall\Webunit\TestCase();
        $assert = new AssertContains('Foo', new Modifiers());
        $assertResult = new AssertResult($assert, false, 'Fail');
        $testCaseResult = new TestCaseResult($testCase, false, $assertResult);

        self::assertSame($testCase, $testCaseResult->getTestCase());
        self::assertFalse($testCaseResult->isSuccess());
        self::assertSame($assertResult, $testCaseResult->getFailedAssertResult());
    }
}
