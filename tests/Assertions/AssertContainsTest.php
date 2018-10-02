<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\FilePath;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
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
            // ModifiersInterface::NONE
            ['Foo', ModifiersInterface::NONE, '', false, 'Content "" does not contain "Foo"'],
            ['Foo', ModifiersInterface::NONE, 'Foo', true, ''],
            ['Foo', ModifiersInterface::NONE, 'foo', false, 'Content "foo" does not contain "Foo"'],
            ['Foo', ModifiersInterface::NONE, 'FooBar', true, ''],
            ['Foo', ModifiersInterface::NONE, 'fooBar', false, 'Content "fooBar" does not contain "Foo"'],
            ['Foo', ModifiersInterface::NONE, 'Bar', false, 'Content "Bar" does not contain "Foo"'],

            // ModifiersInterface::NOT
            ['Foo', ModifiersInterface::NOT, '', true, ''],
            ['Foo', ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" contains "Foo"'],
            ['Foo', ModifiersInterface::NOT, 'foo', true, ''],
            ['Foo', ModifiersInterface::NOT, 'FooBar', false, 'Content "FooBar" contains "Foo"'],
            ['Foo', ModifiersInterface::NOT, 'fooBar', true, ''],
            ['Foo', ModifiersInterface::NOT, 'Bar', true, ''],

            // ModifiersInterface::CASE_INSENSITIVE
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, '', false, 'Content "" does not contain "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'foo', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'FooBar', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'fooBar', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not contain "Foo" (case insensitive)'],

            // ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE
            ['Foo', ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE, '', true, ''],
            ['Foo', ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE, 'Foo', false, 'Content "Foo" contains "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE, 'foo', false, 'Content "foo" contains "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE, 'FooBar', false, 'Content "FooBar" contains "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE, 'fooBar', false, 'Content "fooBar" contains "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE, 'Bar', true, ''],

            // ModifiersInterface::REGEXP
            ['Foo', ModifiersInterface::REGEXP, '', false, 'Content "" does not contain "Foo" (regexp)'],
            ['Foo', ModifiersInterface::REGEXP, 'Foo', true, ''],
            ['Foo', ModifiersInterface::REGEXP, 'Foo Bar', true, ''],
            ['Foo', ModifiersInterface::REGEXP, 'BazFoo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP, 'Foo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP, 'Foo Bar', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP, 'BazFoo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP, 'Foo', false, 'Content "Foo" does not contain "F[O]+" (regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP, 'Bar', false, 'Content "Bar" does not contain "F[O]+" (regexp)'],

            // ModifiersInterface::REGEXP | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, '', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" contains "Foo" (regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "Foo" (regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'BazFoo', false, 'Content "BazFoo" contains "Foo" (regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" contains "F[o]+" (regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "F[o]+" (regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'BazFoo', false, 'Content "BazFoo" contains "F[o]+" (regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Bar', true, ''],

            // ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, '', false, 'Content "" does not contain "Foo" (case insensitive, regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo Bar', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'BazFoo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo Bar', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'BazFoo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not contain "F[O]+" (case insensitive, regexp)'],

            // ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, '', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" contains "Foo" (case insensitive, regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "Foo" (case insensitive, regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'BazFoo', false, 'Content "BazFoo" contains "Foo" (case insensitive, regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" contains "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo Bar', false, 'Content "Foo Bar" contains "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'BazFoo', false, 'Content "BazFoo" contains "F[o]+" (case insensitive, regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" contains "F[O]+" (case insensitive, regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Bar', true, ''],
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
        new AssertContains(new FileLocation(FilePath::parse('/tmp/tests'), 10), '(Foo', new Modifiers(ModifiersInterface::REGEXP));
    }
}
