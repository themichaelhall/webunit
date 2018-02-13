<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use DataTypes\Url;
use MichaelHall\Webunit\TestSuite;
use PHPUnit\Framework\TestCase;

/**
 * Test TestSuite class.
 */
class TestSuiteTest extends TestCase
{
    /**
     * Test empty test suite.
     */
    public function testEmptyTestSuite()
    {
        $testSuite = new TestSuite();

        self::assertSame([], $testSuite->getTestCases());
    }

    /**
     * Test test suite with test cases.
     */
    public function testWithTestCases()
    {
        $testCase1 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost'));
        $testCase2 = new \MichaelHall\Webunit\TestCase(Url::parse('http://localhost/foo'));

        $testSuite = new TestSuite();
        $testSuite->addTestCase($testCase1);
        $testSuite->addTestCase($testCase2);

        self::assertSame([$testCase1, $testCase2], $testSuite->getTestCases());
    }
}
