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

        self::assertTrue($assert->test(new PageResult('FooBar')));
        self::assertFalse($assert->test(new PageResult('fooBar')));
        self::assertTrue($assert->test(new PageResult('Foo')));
        self::assertFalse($assert->test(new PageResult('foo')));
        self::assertFalse($assert->test(new PageResult('Bar')));
    }
}
