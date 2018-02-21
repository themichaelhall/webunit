<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test AssertContains class.
 */
class AssertContainsTest extends TestCase
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
        $assert = (new AssertContains('Foo'));
        $assert->setModifiers(new Modifiers($modifiers));
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
            [Modifiers::NONE, '', false, 'Content "" does not contain "Foo"'],
            [Modifiers::NONE, 'Foo', true, ''],
            [Modifiers::NONE, 'foo', false, 'Content "foo" does not contain "Foo"'],
            [Modifiers::NONE, 'FooBar', true, ''],
            [Modifiers::NONE, 'fooBar', false, 'Content "fooBar" does not contain "Foo"'],
            [Modifiers::NONE, 'Bar', false, 'Content "Bar" does not contain "Foo"'],

            // Modifiers::NOT
            [Modifiers::NOT, '', true, ''],
            [Modifiers::NOT, 'Foo', false, 'Content "Foo" contains "Foo"'],
            [Modifiers::NOT, 'foo', true, ''],
            [Modifiers::NOT, 'FooBar', false, 'Content "FooBar" contains "Foo"'],
            [Modifiers::NOT, 'fooBar', true, ''],
            [Modifiers::NOT, 'Bar', true, ''],

            // Modifiers::CASE_INSENSITIVE
            [Modifiers::CASE_INSENSITIVE, '', false, 'Content "" does not contain "Foo" (case insensitive)'],
            [Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            [Modifiers::CASE_INSENSITIVE, 'foo', true, ''],
            [Modifiers::CASE_INSENSITIVE, 'FooBar', true, ''],
            [Modifiers::CASE_INSENSITIVE, 'fooBar', true, ''],
            [Modifiers::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not contain "Foo" (case insensitive)'],

            // Modifiers::NOT | Modifiers::CASE_INSENSITIVE
            [Modifiers::NOT | Modifiers::CASE_INSENSITIVE, '', true, ''],
            [Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'Foo', false, 'Content "Foo" contains "Foo" (case insensitive)'],
            [Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'foo', false, 'Content "foo" contains "Foo" (case insensitive)'],
            [Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'FooBar', false, 'Content "FooBar" contains "Foo" (case insensitive)'],
            [Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'fooBar', false, 'Content "fooBar" contains "Foo" (case insensitive)'],
            [Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'Bar', true, ''],
        ];
    }
}
