<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\FilePath;
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
     * @param string   $assertContent   The assert content.
     * @param int      $modifiers       The modifiers.
     * @param string[] $headers         The headers.
     * @param bool     $expectedSuccess True the expected result is success, false otherwise.
     * @param string   $expectedError   The expected error.
     */
    public function testAssertion(string $assertContent, int $modifiers, array $headers, bool $expectedSuccess, string $expectedError)
    {
        $location = new FileLocation(FilePath::parse('/tmp/tests'), 10);
        $assert = new AssertHeader($location, $assertContent, new Modifiers($modifiers));
        $pageResult = new PageResult(200, $headers, '');
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
            ['Foo', ModifiersInterface::NONE, [], false, 'Headers "" does not contain a header called "Foo"'],
            ['Foo', ModifiersInterface::NONE, ['Foo'], true, ''],
            ['Foo', ModifiersInterface::NONE, ['Foo: Bar'], true, ''],
            ['Foo', ModifiersInterface::NONE, ['foo'], true, ''],
            ['Foo', ModifiersInterface::NONE, ['foo: Bar'], true, ''],
            ['Foo', ModifiersInterface::NONE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header called "Foo"'],
            ['Foo: Bar', ModifiersInterface::NONE, [], false, 'Headers "" does not contain a header called "Foo" with the value "Bar"'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Foo'], false, 'Headers "Foo" does not contain a header called "Foo" with the value "Bar"'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Foo: Bar'], true, ''],
            ['Foo: Bar', ModifiersInterface::NONE, ['foo'], false, 'Headers "foo" does not contain a header called "Foo" with the value "Bar"'],
            ['Foo: Bar', ModifiersInterface::NONE, ['foo: Bar'], true, ''],
            ['Foo: Bar', ModifiersInterface::NONE, ['foo: bar'], false, 'Headers "foo: bar" does not contain a header called "Foo" with the value "Bar"'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Foo: Baz'], false, 'Headers "Foo: Baz" does not contain a header called "Foo" with the value "Bar"'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Bar: Foo', 'baz: foo'], false, 'Headers "Bar: Foo", "baz: foo" does not contain a header called "Foo" with the value "Bar"'],
            ['Foo: Bar', ModifiersInterface::NONE, ['Bar: Foo', 'Foo: Bar'], true, ''],
        ];
        // todo: Test with all combinations of modifiers.
    }
}
