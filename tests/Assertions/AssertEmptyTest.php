<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\System\FilePath;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Location\FileLocation;
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
        $location = new FileLocation(FilePath::parse('/tmp/tests'), 10);
        $assert = new AssertEmpty($location, new Modifiers($modifiers));
        $pageResult = new PageResult(200, [], $content);
        $result = $assert->test($pageResult);

        self::assertSame($location, $assert->getLocation());
        self::assertSame($expectedSuccess, $result->isSuccess());
        self::assertSame($expectedError, $result->getError());
    }

    /**
     * Data provider for assertion test.
     *
     * @return array The data.
     */
    public function assertionDataProvider(): array
    {
        return [
            // ModifiersInterface::NONE
            [ModifiersInterface::NONE, '', true, ''],
            [ModifiersInterface::NONE, 'Foo', false, 'Content "Foo" is not empty'],
            [ModifiersInterface::NONE, 'foo', false, 'Content "foo" is not empty'],
            [ModifiersInterface::NONE, 'FooBar', false, 'Content "FooBar" is not empty'],
            [ModifiersInterface::NONE, 'fooBar', false, 'Content "fooBar" is not empty'],
            [ModifiersInterface::NONE, 'Bar', false, 'Content "Bar" is not empty'],

            // ModifiersInterface::NOT
            [ModifiersInterface::NOT, '', false, 'Content "" is empty'],
            [ModifiersInterface::NOT, 'Foo', true, ''],
            [ModifiersInterface::NOT, 'foo', true, ''],
            [ModifiersInterface::NOT, 'FooBar', true, ''],
            [ModifiersInterface::NOT, 'fooBar', true, ''],
            [ModifiersInterface::NOT, 'Bar', true, ''],
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
            new AssertEmpty(new FileLocation(FilePath::parse('/tmp/tests'), 10), new Modifiers($modifiers));
        } catch (NotAllowedModifierException $exception) {
        }

        self::assertTrue($exception->getModifiers()->equals(new Modifiers($expectedInvalidModifiers)));
    }

    /**
     * Data provider for assertion with not allowed modifiers test.
     *
     * @return array The data.
     */
    public function assertionWithNotAllowedModifierDataProvider(): array
    {
        return [
            [ModifiersInterface::CASE_INSENSITIVE, ModifiersInterface::CASE_INSENSITIVE],
            [ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ModifiersInterface::CASE_INSENSITIVE],
            [ModifiersInterface::REGEXP, ModifiersInterface::REGEXP],
            [ModifiersInterface::REGEXP | ModifiersInterface::NOT, ModifiersInterface::REGEXP],
            [ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE],
            [ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE],
        ];
    }
}
