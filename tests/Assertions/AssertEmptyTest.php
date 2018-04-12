<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test AssertEmpty class.
 */
class AssertEmptyTest extends TestCase
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
        $assert = new AssertEmpty(new Modifiers($modifiers));
        $pageResult = new PageResult(200, $content);
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
            [Modifiers::NONE, '', true, ''],
            [Modifiers::NONE, 'Foo', false, 'Content "Foo" is not empty'],
            [Modifiers::NONE, 'foo', false, 'Content "foo" is not empty'],
            [Modifiers::NONE, 'FooBar', false, 'Content "FooBar" is not empty'],
            [Modifiers::NONE, 'fooBar', false, 'Content "fooBar" is not empty'],
            [Modifiers::NONE, 'Bar', false, 'Content "Bar" is not empty'],

            // Modifiers::NOT
            [Modifiers::NOT, '', false, 'Content "" is empty'],
            [Modifiers::NOT, 'Foo', true, ''],
            [Modifiers::NOT, 'foo', true, ''],
            [Modifiers::NOT, 'FooBar', true, ''],
            [Modifiers::NOT, 'fooBar', true, ''],
            [Modifiers::NOT, 'Bar', true, ''],
        ];
    }

    /**
     * Test assertion with not allowed modifier.
     *
     * @dataProvider assertionWithNotAllowedModifierDataProvider
     *
     * @param int $modifiers                The modifiers.
     * @param int $expectedInvalidModifiers The expected invalid modifiers.
     */
    public function testAssertionWithNotAllowedModifier(int $modifiers, int $expectedInvalidModifiers)
    {
        $exception = null;

        try {
            new AssertEmpty(new Modifiers($modifiers));
        } catch (NotAllowedModifierException $exception) {
        }

        self::assertTrue($exception->getModifiers()->equals(new Modifiers($expectedInvalidModifiers)));
    }

    /**
     * Data provider for assertion with not allowed modifiers test.
     *
     * @return array The data.
     */
    public function assertionWithNotAllowedModifierDataProvider()
    {
        return [
            [Modifiers::CASE_INSENSITIVE, Modifiers::CASE_INSENSITIVE],
            [Modifiers::CASE_INSENSITIVE | Modifiers::NOT, Modifiers::CASE_INSENSITIVE],
            [Modifiers::REGEXP, Modifiers::REGEXP],
            [Modifiers::REGEXP | Modifiers::NOT, Modifiers::REGEXP],
            [Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE],
            [Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE],
        ];
    }
}
