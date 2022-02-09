<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\System\FilePath;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Exceptions\InvalidRegexpException;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Location\FileLocation;
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
        $location = new FileLocation(FilePath::parse('/tmp/tests'), 10);
        $assert = new AssertEquals($location, $assertContent, new Modifiers($modifiers));
        $pageResult = new PageResult(200, [], $content);
        $result = $assert->test($pageResult);

        self::assertSame($location, $assert->getLocation());
        self::assertSame($assertContent, $assert->getContent());
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
            ['Foo', ModifiersInterface::NONE, '', false, 'Content "" does not equal "Foo"'],
            ['Foo', ModifiersInterface::NONE, 'Foo', true, ''],
            ['Foo', ModifiersInterface::NONE, 'foo', false, 'Content "foo" does not equal "Foo"'],
            ['Foo', ModifiersInterface::NONE, 'FooBar', false, 'Content "FooBar" does not equal "Foo"'],
            ['Foo', ModifiersInterface::NONE, 'fooBar', false, 'Content "fooBar" does not equal "Foo"'],
            ['Foo', ModifiersInterface::NONE, 'Bar', false, 'Content "Bar" does not equal "Foo"'],

            // ModifiersInterface::NOT
            ['Foo', ModifiersInterface::NOT, '', true, ''],
            ['Foo', ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" equals "Foo"'],
            ['Foo', ModifiersInterface::NOT, 'foo', true, ''],
            ['Foo', ModifiersInterface::NOT, 'FooBar', true, ''],
            ['Foo', ModifiersInterface::NOT, 'fooBar', true, ''],
            ['Foo', ModifiersInterface::NOT, 'Bar', true, ''],

            // ModifiersInterface::CASE_INSENSITIVE
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, '', false, 'Content "" does not equal "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'foo', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'FooBar', false, 'Content "FooBar" does not equal "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'fooBar', false, 'Content "fooBar" does not equal "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not equal "Foo" (case insensitive)'],

            // ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, '', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" equals "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'foo', false, 'Content "foo" equals "Foo" (case insensitive)'],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'FooBar', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'fooBar', true, ''],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Bar', true, ''],

            // ModifiersInterface::REGEXP
            ['Foo', ModifiersInterface::REGEXP, '', false, 'Content "" does not equal "Foo" (regexp)'],
            ['Foo', ModifiersInterface::REGEXP, 'Foo', true, ''],
            ['Foo', ModifiersInterface::REGEXP, 'Foo Bar', false, 'Content "Foo Bar" does not equal "Foo" (regexp)'],
            ['Foo', ModifiersInterface::REGEXP, 'BazFoo', false, 'Content "BazFoo" does not equal "Foo" (regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP, 'Foo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP, 'Foo Bar', false, 'Content "Foo Bar" does not equal "F[o]+" (regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP, 'BazFoo', false, 'Content "BazFoo" does not equal "F[o]+" (regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP, 'Foo', false, 'Content "Foo" does not equal "F[O]+" (regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP, 'Bar', false, 'Content "Bar" does not equal "F[O]+" (regexp)'],

            // ModifiersInterface::REGEXP | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, '', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" equals "Foo" (regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo Bar', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'BazFoo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" equals "F[o]+" (regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo Bar', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'BazFoo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Foo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, 'Bar', true, ''],

            // ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, '', false, 'Content "" does not equal "Foo" (case insensitive, regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo Bar', false, 'Content "Foo Bar" does not equal "Foo" (case insensitive, regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'BazFoo', false, 'Content "BazFoo" does not equal "Foo" (case insensitive, regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo Bar', false, 'Content "Foo Bar" does not equal "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'BazFoo', false, 'Content "BazFoo" does not equal "F[o]+" (case insensitive, regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Foo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, 'Bar', false, 'Content "Bar" does not equal "F[O]+" (case insensitive, regexp)'],

            // ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, '', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" equals "Foo" (case insensitive, regexp)'],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo Bar', true, ''],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'BazFoo', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" equals "F[o]+" (case insensitive, regexp)'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo Bar', true, ''],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'BazFoo', true, ''],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Foo', false, 'Content "Foo" equals "F[O]+" (case insensitive, regexp)'],
            ['F[O]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, 'Bar', true, ''],
        ];
    }

    /**
     * Test invalid regexp.
     */
    public function testInvalidRegexp()
    {
        self::expectException(InvalidRegexpException::class);
        self::expectExceptionMessage('Regexp "(Foo" is invalid.');

        new AssertEquals(new FileLocation(FilePath::parse('/tmp/tests'), 10), '(Foo', new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP));
    }
}
