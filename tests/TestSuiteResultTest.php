<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\FilePath;
use DataTypes\Url;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\AssertResult;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\TestCaseResult;
use MichaelHall\Webunit\TestSuite;
use MichaelHall\Webunit\TestSuiteResult;
use PHPUnit\Framework\TestCase;

/**
 * Test TestSuiteResult class.
 */
class TestSuiteResultTest extends TestCase
{
    /**
     * Test empty result.
     */
    public function testEmptyResult()
    {
        $testSuite = new TestSuite();
        $testSuiteResult = new TestSuiteResult($testSuite, []);

        self::assertSame($testSuite, $testSuiteResult->getTestSuite());
        self::assertSame([], $testSuiteResult->getTestCaseResults());
        self::assertSame([], $testSuiteResult->getFailedTestCaseResults());
        self::assertTrue($testSuiteResult->isSuccess());
        self::assertSame(0, $testSuiteResult->getFailedTestsCount());
    }

    /**
     * Test successful result.
     */
    public function testSuccessfulResult()
    {
        $testSuite = new TestSuite();

        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase1 = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost/'));
        $testCase2 = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost/foo'));
        $testCaseResult1 = new TestCaseResult($testCase1);
        $testCaseResult2 = new TestCaseResult($testCase2);

        $testSuiteResult = new TestSuiteResult($testSuite, [$testCaseResult1, $testCaseResult2]);

        self::assertSame($testSuite, $testSuiteResult->getTestSuite());
        self::assertSame([$testCaseResult1, $testCaseResult2], $testSuiteResult->getTestCaseResults());
        self::assertSame([], $testSuiteResult->getFailedTestCaseResults());
        self::assertTrue($testSuiteResult->isSuccess());
        self::assertSame(0, $testSuiteResult->getFailedTestsCount());
    }

    /**
     * Test unsuccessful result.
     */
    public function testUnsuccessfulResult()
    {
        $testSuite = new TestSuite();

        $location = new FileLocation(FilePath::parse('./foo.webunit'), 1);

        $testCase1 = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost/'));
        $testCase2 = new \MichaelHall\Webunit\TestCase($location, Url::parse('http://localhost/foo'));
        $assert1 = new AssertContains($location, 'Foo', new Modifiers());
        $assertResult1 = new AssertResult($assert1, false, 'Fail');
        $testCaseResult1 = new TestCaseResult($testCase1, $assertResult1);
        $testCaseResult2 = new TestCaseResult($testCase2);

        $testSuiteResult = new TestSuiteResult($testSuite, [$testCaseResult1, $testCaseResult2]);

        self::assertSame($testSuite, $testSuiteResult->getTestSuite());
        self::assertSame([$testCaseResult1, $testCaseResult2], $testSuiteResult->getTestCaseResults());
        self::assertSame([$testCaseResult1], $testSuiteResult->getFailedTestCaseResults());
        self::assertFalse($testSuiteResult->isSuccess());
        self::assertSame(1, $testSuiteResult->getFailedTestsCount());
    }
}
