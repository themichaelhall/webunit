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
     * @param string $assertContent   The assert content.
     * @param int    $modifiers       The modifiers.
     * @param string $content         The content.
     * @param bool   $expectedSuccess True the expected result is success, false otherwise.
     * @param string $expectedError   The expected error.
     */
    public function testAssertion(string $assertContent, int $modifiers, string $content, bool $expectedSuccess, string $expectedError)
    {
        $assert = new AssertEquals($assertContent, new Modifiers($modifiers));
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
            ['Foo', Modifiers::NONE, '', false, 'Content "" does not equal "Foo"'],
            ['Foo', Modifiers::NONE, 'Foo', true, ''],
            ['Foo', Modifiers::NONE, 'foo', false, 'Content "foo" does not equal "Foo"'],
            ['Foo', Modifiers::NONE, 'FooBar', false, 'Content "FooBar" does not equal "Foo"'],
            ['Foo', Modifiers::NONE, 'fooBar', false, 'Content "fooBar" does not equal "Foo"'],
            ['Foo', Modifiers::NONE, 'Bar', false, 'Content "Bar" does not equal "Foo"'],

            // Modifiers::NOT
            ['Foo', Modifiers::NOT, '', true, ''],
            ['Foo', Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "Foo"'],
            ['Foo', Modifiers::NOT, 'foo', true, ''],
            ['Foo', Modifiers::NOT, 'FooBar', true, ''],
            ['Foo', Modifiers::NOT, 'fooBar', true, ''],
            ['Foo', Modifiers::NOT, 'Bar', true, ''],

            // Modifiers::CASE_INSENSITIVE
            ['Foo', Modifiers::CASE_INSENSITIVE, '', false, 'Content "" does not equal "Foo" (case insensitive)'],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'foo', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'FooBar', false, 'Content "FooBar" does not equal "Foo" (case insensitive)'],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'fooBar', false, 'Content "fooBar" does not equal "Foo" (case insensitive)'],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not equal "Foo" (case insensitive)'],

            // Modifiers::CASE_INSENSITIVE | Modifiers::NOT
            ['Foo', Modifiers::CASE_INSENSITIVE | Modifiers::NOT, '', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "Foo" (case insensitive)'],
            ['Foo', Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'foo', false, 'Content "foo" equals "Foo" (case insensitive)'],
            ['Foo', Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'FooBar', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'fooBar', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Bar', true, ''],

            // Modifiers::REGEXP
            ['Foo', Modifiers::REGEXP, '', false, 'Content "" does not equal "Foo" (regexp)'],
            ['Foo', Modifiers::REGEXP, 'Foo', true, ''],
            ['Foo', Modifiers::REGEXP, 'Foo Bar', false, 'Content "Foo Bar" does not equal "Foo" (regexp)'],
            ['Foo', Modifiers::REGEXP, 'BazFoo', false, 'Content "BazFoo" does not equal "Foo" (regexp)'],
            ['F[o]+', Modifiers::REGEXP, 'Foo', true, ''],
            ['F[o]+', Modifiers::REGEXP, 'Foo Bar', false, 'Content "Foo Bar" does not equal "F[o]+" (regexp)'],
            ['F[o]+', Modifiers::REGEXP, 'BazFoo', false, 'Content "BazFoo" does not equal "F[o]+" (regexp)'],
            ['F[O]+', Modifiers::REGEXP, 'Foo', false, 'Content "Foo" does not equal "F[O]+" (regexp)'],
            ['F[O]+', Modifiers::REGEXP, 'Bar', false, 'Content "Bar" does not equal "F[O]+" (regexp)'],

            // Modifiers::REGEXP | Modifiers::NOT
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, '', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "Foo" (regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, 'Foo Bar', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, 'BazFoo', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "F[o]+" (regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::NOT, 'Foo Bar', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::NOT, 'BazFoo', true, ''],
            ['F[O]+', Modifiers::REGEXP | Modifiers::NOT, 'Foo', true, ''],
            ['F[O]+', Modifiers::REGEXP | Modifiers::NOT, 'Bar', true, ''],

            // Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, '', false, 'Content "" does not equal "Foo" (case insensitive, regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo Bar', false, 'Content "Foo Bar" does not equal "Foo" (case insensitive, regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'BazFoo', false, 'Content "BazFoo" does not equal "Foo" (case insensitive, regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo Bar', false, 'Content "Foo Bar" does not equal "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'BazFoo', false, 'Content "BazFoo" does not equal "F[o]+" (case insensitive, regexp)'],
            ['F[O]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[O]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not equal "F[O]+" (case insensitive, regexp)'],

            // Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, '', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "Foo" (case insensitive, regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo Bar', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'BazFoo', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo Bar', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'BazFoo', true, ''],
            ['F[O]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo', false, 'Content "Foo" equals "F[O]+" (case insensitive, regexp)'],
            ['F[O]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Bar', true, ''],
        ];
    }

    /**
     * Test invalid regexp.
     *
     * @expectedException \MichaelHall\Webunit\Exceptions\InvalidRegexpException
     * @expectedExceptionMessage Regexp "(Foo" is invalid.
     */
    public function testInvalidRegexp()
    {
        new AssertEquals('(Foo', new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP));
    }
}
