<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test AssertContains class.
 */
class AssertContainsTest extends TestCase
{
    /**
     * Test assertion.
     */
    public function testAssertion()
    {
        $assert = new AssertContains('Foo');

        self::assertTrue($assert->test(new PageResult('FooBar'))->isSuccess());
        self::assertFalse($assert->test(new PageResult('fooBar'))->isSuccess());
        self::assertTrue($assert->test(new PageResult('Foo'))->isSuccess());
        self::assertFalse($assert->test(new PageResult('foo'))->isSuccess());
        self::assertFalse($assert->test(new PageResult('Bar'))->isSuccess());
    }

    /**
     * Test error text on failure.
     */
    public function testErrorTextOnFailure()
    {
        $assert = new AssertContains('Foo');
        $result = $assert->test(new PageResult('Bar'));

        self::assertSame('Content "Bar" does not contain "Foo"', $result->getError());
    }
}
