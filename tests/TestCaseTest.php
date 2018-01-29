<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertContainsNot;
use PHPUnit\Framework\TestCase;

/**
 * Test TestCase class.
 */
class TestCaseTest extends TestCase
{
    /**
     * Test empty test case.
     */
    public function testEmptyTestCase()
    {
        $testCase = new \MichaelHall\Webunit\TestCase();

        self::assertSame([], $testCase->getAsserts());
    }

    /**
     * Test test case with asserts.
     */
    public function testWithAsserts()
    {
        $assert1 = new AssertContains('Foo');
        $assert2 = new AssertContainsNot('Bar');

        $testCase = new \MichaelHall\Webunit\TestCase();
        $testCase->addAssert($assert1);
        $testCase->addAssert($assert2);

        self::assertSame([$assert1, $assert2], $testCase->getAsserts());
    }
}
