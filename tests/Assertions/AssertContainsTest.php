<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\FilePath;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Location\FileLocation;
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
     * @param string $assertContent   The assert content.
     * @param int    $modifiers       The modifiers.
     * @param string $content         The content.
     * @param bool   $expectedSuccess True the expected result is success, false otherwise.
     * @param string $expectedError   The expected error.
     */
    public function testAssertion(string $assertContent, int $modifiers, string $content, bool $expectedSuccess, string $expectedError)
    {
        $location = new FileLocation(FilePath::parse('/tmp/tests'), 10);
        $assert = new AssertContains($location, $assertContent, new Modifiers($modifiers));
        $pageResult = new PageResult(200, $content);
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
    public function assertionDataProvider()
    {
        return [
            // Modifiers::NONE
            ['Foo', Modifiers::NONE, '', false, 'Content "" does not contain "Foo"'],
            ['Foo', Modifiers::NONE, 'Foo', true, ''],
            ['Foo', Modifiers::NONE, 'foo', false, 'Content "foo" does not contain "Foo"'],
            ['Foo', Modifiers::NONE, 'FooBar', true, ''],
            ['Foo', Modifiers::NONE, 'fooBar', false, 'Content "fooBar" does not contain "Foo"'],
            ['Foo', Modifiers::NONE, 'Bar', false, 'Content "Bar" does not contain "Foo"'],

            // Modifiers::NOT
            ['Foo', Modifiers::NOT, '', true, ''],
            ['Foo', Modifiers::NOT, 'Foo', false, 'Content "Foo" contains "Foo"'],
            ['Foo', Modifiers::NOT, 'foo', true, ''],
            ['Foo', Modifiers::NOT, 'FooBar', false, 'Content "FooBar" contains "Foo"'],
            ['Foo', Modifiers::NOT, 'fooBar', true, ''],
            ['Foo', Modifiers::NOT, 'Bar', true, ''],

            // Modifiers::CASE_INSENSITIVE
            ['Foo', Modifiers::CASE_INSENSITIVE, '', false, 'Content "" does not contain "Foo" (case insensitive)'],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'foo', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'FooBar', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'fooBar', true, ''],
            ['Foo', Modifiers::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not contain "Foo" (case insensitive)'],

            // Modifiers::NOT | Modifiers::CASE_INSENSITIVE
            ['Foo', Modifiers::NOT | Modifiers::CASE_INSENSITIVE, '', true, ''],
            ['Foo', Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'Foo', false, 'Content "Foo" contains "Foo" (case insensitive)'],
            ['Foo', Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'foo', false, 'Content "foo" contains "Foo" (case insensitive)'],
            ['Foo', Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'FooBar', false, 'Content "FooBar" contains "Foo" (case insensitive)'],
            ['Foo', Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'fooBar', false, 'Content "fooBar" contains "Foo" (case insensitive)'],
            ['Foo', Modifiers::NOT | Modifiers::CASE_INSENSITIVE, 'Bar', true, ''],

            // Modifiers::REGEXP
            ['Foo', Modifiers::REGEXP, '', false, 'Content "" does not contain "Foo" (regexp)'],
            ['Foo', Modifiers::REGEXP, 'Foo', true, ''],
            ['Foo', Modifiers::REGEXP, 'Foo Bar', true, ''],
            ['Foo', Modifiers::REGEXP, 'BazFoo', true, ''],
            ['F[o]+', Modifiers::REGEXP, 'Foo', true, ''],
            ['F[o]+', Modifiers::REGEXP, 'Foo Bar', true, ''],
            ['F[o]+', Modifiers::REGEXP, 'BazFoo', true, ''],
            ['F[O]+', Modifiers::REGEXP, 'Foo', false, 'Content "Foo" does not contain "F[O]+" (regexp)'],
            ['F[O]+', Modifiers::REGEXP, 'Bar', false, 'Content "Bar" does not contain "F[O]+" (regexp)'],

            // Modifiers::REGEXP | Modifiers::NOT
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, '', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, 'Foo', false, 'Content "Foo" contains "Foo" (regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "Foo" (regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::NOT, 'BazFoo', false, 'Content "BazFoo" contains "Foo" (regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::NOT, 'Foo', false, 'Content "Foo" contains "F[o]+" (regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "F[o]+" (regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::NOT, 'BazFoo', false, 'Content "BazFoo" contains "F[o]+" (regexp)'],
            ['F[O]+', Modifiers::REGEXP | Modifiers::NOT, 'Foo', true, ''],
            ['F[O]+', Modifiers::REGEXP | Modifiers::NOT, 'Bar', true, ''],

            // Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, '', false, 'Content "" does not contain "Foo" (case insensitive, regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo Bar', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'BazFoo', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo Bar', true, ''],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'BazFoo', true, ''],
            ['F[O]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[O]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not contain "F[O]+" (case insensitive, regexp)'],

            // Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, '', true, ''],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo', false, 'Content "Foo" contains "Foo" (case insensitive, regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "Foo" (case insensitive, regexp)'],
            ['Foo', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'BazFoo', false, 'Content "BazFoo" contains "Foo" (case insensitive, regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo', false, 'Content "Foo" contains "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'BazFoo', false, 'Content "BazFoo" contains "F[o]+" (case insensitive, regexp)'],
            ['F[O]+', Modifiers::REGEXP | Modifiers::CASE_INSENSITIVE | Modifiers::NOT, 'Foo', false, 'Content "Foo" contains "F[O]+" (case insensitive, regexp)'],
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
        new AssertContains(new FileLocation(FilePath::parse('/tmp/tests'), 10), '(Foo', new Modifiers(Modifiers::REGEXP));
    }
}
