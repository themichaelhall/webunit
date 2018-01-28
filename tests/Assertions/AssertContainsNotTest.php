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

        self::assertFalse($assert->test(new PageResult('FooBar')));
        self::assertTrue($assert->test(new PageResult('fooBar')));
        self::assertFalse($assert->test(new PageResult('Foo')));
        self::assertTrue($assert->test(new PageResult('foo')));
        self::assertTrue($assert->test(new PageResult('Bar')));
    }
}
