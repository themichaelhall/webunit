<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use MichaelHall\Webunit\Assertions\AssertEmpty;
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
}
