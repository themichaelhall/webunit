<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\System\FilePath;
use MichaelHall\Webunit\Assertions\AssertHeader;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test AssertHeader class.
 */
class AssertHeaderTest extends TestCase
{
    /**
     * Test assertion.
     *
     * @dataProvider assertionDataProvider
     *
     * @param string      $assertContent       The assert content.
     * @param int         $modifiers           The modifiers.
     * @param string[]    $headers             The headers.
     * @param bool        $expectedSuccess     True the expected result is success, false otherwise.
     * @param string      $expectedError       The expected error.
     * @param string      $expectedHeaderName  The expected header name.
     * @param string|null $expectedHeaderValue The expected header value or null if no header value is expected.
     */
    public function testAssertion(string $assertContent, int $modifiers, array $headers, bool $expectedSuccess, string $expectedError, string $expectedHeaderName, ?string $expectedHeaderValue)
    {
        $location = new FileLocation(FilePath::parse('/tmp/tests'), 10);
        $assert = new AssertHeader($location, $assertContent, new Modifiers($modifiers));
        $pageResult = new PageResult(200, $headers, '');
        $result = $assert->test($pageResult);

        self::assertSame($location, $assert->getLocation());
        self::assertSame($expectedHeaderName, $assert->getHeaderName());
        self::assertSame($expectedHeaderValue, $assert->getHeaderValue());
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
            ['Foo', ModifiersInterface::NONE, [], false, 'Headers "" does not contain a header with name "Foo"', 'Foo', null],
            ['Foo', ModifiersInterface::NONE, ['Foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::NONE, ['Foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::NONE, ['foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::NONE, ['foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::NONE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo"', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::NONE, [], false, 'Headers "" does not contain a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['foo'], false, 'Headers "foo" does not contain a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Foo: Baz'], false, 'Headers "Foo: Baz" does not contain a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Bar: Foo', 'Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::NONE, [], false, 'Headers "" does not contain a header with name "F[o]+"', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NONE, [], false, 'Headers "" does not contain a header with name "F[o]+" and value "B[ar]+"', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NONE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "F[o]+"', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NONE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "F[o]+" and value "B[ar]+"', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NONE, ['foo'], false, 'Headers "foo" does not contain a header with name "F[o]+"', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NONE, ['foo'], false, 'Headers "foo" does not contain a header with name "F[o]+" and value "B[ar]+"', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NONE, ['Foo: Bar'], false, 'Headers "Foo: Bar" does not contain a header with name "F[o]+"', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NONE, ['Foo: Bar'], false, 'Headers "Foo: Bar" does not contain a header with name "F[o]+" and value "B[ar]+"', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NONE, ['Foo: bar'], false, 'Headers "Foo: bar" does not contain a header with name "F[o]+"', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NONE, ['Foo: bar'], false, 'Headers "Foo: bar" does not contain a header with name "F[o]+" and value "B[ar]+"', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NONE, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header with name "F[o]+"', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NONE, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header with name "F[o]+" and value "B[ar]+"', 'F[o]+', 'B[ar]+'],

            // ModifiersInterface::NOT
            ['Foo', ModifiersInterface::NOT, [], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::NOT, ['Foo'], false, 'Headers "Foo" contains a header with name "Foo"', 'Foo', null],
            ['Foo', ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo"', 'Foo', null],
            ['Foo', ModifiersInterface::NOT, ['foo'], false, 'Headers "foo" contains a header with name "Foo"', 'Foo', null],
            ['Foo', ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo"', 'Foo', null],
            ['Foo', ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::NOT, [], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['Foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['foo: bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['Foo: Baz'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::NOT, ['Bar: Foo', 'Foo: Bar'], false, 'Headers "Bar: Foo", "Foo: Bar" contains a header with name "Foo" and value "Bar"', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::NOT, [], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NOT, [], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NOT, ['Foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NOT, ['Foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NOT, ['foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NOT, ['foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NOT, ['Foo: Bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NOT, ['Foo: Bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NOT, ['Foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NOT, ['Foo: bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::NOT, ['foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::NOT, ['foo: bar'], true, '', 'F[o]+', 'B[ar]+'],

            // ModifiersInterface::CASE_INSENSITIVE
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "Foo" (case insensitive)', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, ['Foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, ['foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, ['foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo" (case insensitive)', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['foo'], false, 'Headers "foo" does not contain a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['foo: bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['Foo: Baz'], false, 'Headers "Foo: Baz" does not contain a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE, ['Bar: Foo', 'Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "F[o]+" (case insensitive)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "F[o]+" (case insensitive)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE, ['foo'], false, 'Headers "foo" does not contain a header with name "F[o]+" (case insensitive)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE, ['foo'], false, 'Headers "foo" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], false, 'Headers "Foo: Bar" does not contain a header with name "F[o]+" (case insensitive)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], false, 'Headers "Foo: Bar" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE, ['Foo: bar'], false, 'Headers "Foo: bar" does not contain a header with name "F[o]+" (case insensitive)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE, ['Foo: bar'], false, 'Headers "Foo: bar" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header with name "F[o]+" (case insensitive)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive)', 'F[o]+', 'B[ar]+'],

            // ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], false, 'Headers "Foo" contains a header with name "Foo" (case insensitive)', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo" (case insensitive)', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], false, 'Headers "foo" contains a header with name "Foo" (case insensitive)', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo" (case insensitive)', 'Foo', null],
            ['Foo', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: bar'], false, 'Headers "foo: bar" contains a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Baz'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Bar: Foo', 'Foo: Bar'], false, 'Headers "Bar: Foo", "Foo: Bar" contains a header with name "Foo" and value "Bar" (case insensitive)', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: bar'], true, '', 'F[o]+', 'B[ar]+'],

            // ModifiersInterface::REGEXP
            ['Foo', ModifiersInterface::REGEXP, [], false, 'Headers "" does not contain a header with name "Foo" (regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP, ['Foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP, ['Foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP, ['foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP, ['foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo" (regexp)', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::REGEXP, [], false, 'Headers "" does not contain a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['Foo'], false, 'Headers "Foo" does not contain a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['foo'], false, 'Headers "foo" does not contain a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['Foo: Baz'], false, 'Headers "Foo: Baz" does not contain a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP, ['Bar: Foo', 'Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::REGEXP, [], false, 'Headers "" does not contain a header with name "F[o]+" (regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP, [], false, 'Headers "" does not contain a header with name "F[o]+" and value "B[ar]+" (regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP, ['Foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP, ['Foo'], false, 'Headers "Foo" does not contain a header with name "F[o]+" and value "B[ar]+" (regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP, ['foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP, ['foo'], false, 'Headers "foo" does not contain a header with name "F[o]+" and value "B[ar]+" (regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP, ['Foo: Bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP, ['Foo: Bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP, ['Foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP, ['Foo: bar'], false, 'Headers "Foo: bar" does not contain a header with name "F[o]+" and value "B[ar]+" (regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP, ['foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header with name "F[o]+" and value "B[ar]+" (regexp)', 'F[o]+', 'B[ar]+'],

            // ModifiersInterface::REGEXP | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, [], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo'], false, 'Headers "Foo" contains a header with name "Foo" (regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo" (regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo'], false, 'Headers "foo" contains a header with name "Foo" (regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo" (regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, [], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo: bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo: Baz'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Bar: Foo', 'Foo: Bar'], false, 'Headers "Bar: Foo", "Foo: Bar" contains a header with name "Foo" and value "Bar" (regexp)', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, [], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, [], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo'], false, 'Headers "Foo" contains a header with name "F[o]+" (regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo'], false, 'Headers "foo" contains a header with name "F[o]+" (regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "F[o]+" (regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "F[o]+" and value "B[ar]+" (regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo: bar'], false, 'Headers "Foo: bar" contains a header with name "F[o]+" (regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['Foo: bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo: bar'], false, 'Headers "foo: bar" contains a header with name "F[o]+" (regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::NOT, ['foo: bar'], true, '', 'F[o]+', 'B[ar]+'],

            // ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "Foo" (case insensitive, regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo: Bar'], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo" (case insensitive, regexp)', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo'], false, 'Headers "foo" does not contain a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo: Bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo: bar'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo: Baz'], false, 'Headers "Foo: Baz" does not contain a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Bar: Foo', 'Foo: Bar'], true, '', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "F[o]+" (case insensitive, regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, [], false, 'Headers "" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive, regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo'], false, 'Headers "Foo" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive, regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo'], false, 'Headers "foo" does not contain a header with name "F[o]+" and value "B[ar]+" (case insensitive, regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo: Bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['Foo: bar'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo: bar'], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE, ['foo: bar'], true, '', 'F[o]+', 'B[ar]+'],

            // ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], false, 'Headers "Foo" contains a header with name "Foo" (case insensitive, regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo" (case insensitive, regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], false, 'Headers "foo" contains a header with name "Foo" (case insensitive, regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo" (case insensitive, regexp)', 'Foo', null],
            ['Foo', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', null],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: Bar'], false, 'Headers "foo: Bar" contains a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: bar'], false, 'Headers "foo: bar" contains a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Baz'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Bar: Foo', 'baz: foo'], true, '', 'Foo', 'Bar'],
            ['Foo: Bar', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Bar: Foo', 'Foo: Bar'], false, 'Headers "Bar: Foo", "Foo: Bar" contains a header with name "Foo" and value "Bar" (case insensitive, regexp)', 'Foo', 'Bar'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, [], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], false, 'Headers "Foo" contains a header with name "F[o]+" (case insensitive, regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], false, 'Headers "foo" contains a header with name "F[o]+" (case insensitive, regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo'], true, '', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "F[o]+" (case insensitive, regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: Bar'], false, 'Headers "Foo: Bar" contains a header with name "F[o]+" and value "B[ar]+" (case insensitive, regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: bar'], false, 'Headers "Foo: bar" contains a header with name "F[o]+" (case insensitive, regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['Foo: bar'], false, 'Headers "Foo: bar" contains a header with name "F[o]+" and value "B[ar]+" (case insensitive, regexp)', 'F[o]+', 'B[ar]+'],
            ['F[o]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: bar'], false, 'Headers "foo: bar" contains a header with name "F[o]+" (case insensitive, regexp)', 'F[o]+', null],
            ['F[o]+: B[ar]+', ModifiersInterface::REGEXP | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, ['foo: bar'], false, 'Headers "foo: bar" contains a header with name "F[o]+" and value "B[ar]+" (case insensitive, regexp)', 'F[o]+', 'B[ar]+'],
        ];
    }
}
