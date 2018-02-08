<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use MichaelHall\Webunit\Assertions\AssertContainsNot;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test AssertContainsNot class.
 */
class AssertContainsNotTest extends TestCase
{
    /**
     * Test assertion.
     */
    public function testAssertion()
    {
        $assert = new AssertContainsNot('Foo');

        self::assertFalse($assert->test(new PageResult('FooBar'))->isSuccess());
        self::assertTrue($assert->test(new PageResult('fooBar'))->isSuccess());
        self::assertFalse($assert->test(new PageResult('Foo'))->isSuccess());
        self::assertTrue($assert->test(new PageResult('foo'))->isSuccess());
        self::assertTrue($assert->test(new PageResult('Bar'))->isSuccess());
    }

    /**
     * Test error text on failure.
     */
    public function testErrorTextOnFailure()
    {
        $assert = new AssertContainsNot('Foo');
        $result = $assert->test(new PageResult('Foo Bar'));

        self::assertSame('Content "Foo Bar" contains "Foo"', $result->getError());
    }
}
