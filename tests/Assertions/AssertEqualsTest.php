<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test AssertEquals class.
 */
class AssertEqualsTest extends TestCase
{
    /**
     * Test assertion.
     *
     * @dataProvider assertionDataProvider
     *
     * @param int    $modifiers       The modifiers.
     * @param string $content         The content.
     * @param bool   $expectedSuccess True the expected result is success, false otherwise.
     * @param string $expectedError   The expected error.
     */
    public function testAssertion(int $modifiers, string $content, bool $expectedSuccess, string $expectedError)
    {
        $assert = new AssertEquals('Foo', new Modifiers($modifiers));
        $pageResult = new PageResult($content);
        $result = $assert->test($pageResult);

        self::assertSame($expectedSuccess, $result->isSuccess());
        self::assertSame($expectedError, $result->getError());
    }

    /**
     * Data provider for assertion test.
     *
     * @return array The data.
     */
    public function assertionDataProvider()
    {
        return [
            // Modifiers::NONE
            [Modifiers::NONE, '', false, 'Content "" does not equal "Foo"'],
            [Modifiers::NONE, 'Foo', true, ''],
            [Modifiers::NONE, 'foo', false, 'Content "foo" does not equal "Foo"'],
            [Modifiers::NONE, 'FooBar', false, 'Content "FooBar" does not equal "Foo"'],
            [Modifiers::NONE, 'fooBar', false, 'Content "fooBar" does not equal "Foo"'],
            [Modifiers::NONE, 'Bar', false, 'Content "Bar" does not equal "Foo"'],

            // Modifiers::NOT
            [Modifiers::NOT, '', true, ''],
            [Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "Foo"'],
            [Modifiers::NOT, 'foo', true, ''],
            [Modifiers::NOT, 'FooBar', true, ''],
            [Modifiers::NOT, 'fooBar', true, ''],
            [Modifiers::NOT, 'Bar', true, ''],
        ];
    }
}
