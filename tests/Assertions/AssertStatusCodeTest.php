<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\FilePath;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test AssertStatusCode class.
 */
class AssertStatusCodeTest extends TestCase
{
    /**
     * Test assertion.
     *
     * @dataProvider assertionDataProvider
     *
     * @param int    $modifiers       The modifiers.
     * @param int    $statusCode      The status code.
     * @param bool   $expectedSuccess True the expected result is success, false otherwise.
     * @param string $expectedError   The expected error.
     */
    public function testAssertion(int $modifiers, int $statusCode, bool $expectedSuccess, string $expectedError)
    {
        $location = new FileLocation(FilePath::parse('/tmp/tests'), 10);
        $assert = new AssertStatusCode($location, 200, new Modifiers($modifiers));
        $pageResult = new PageResult($statusCode, '');
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
            [Modifiers::NONE, 200, true, ''],
            [Modifiers::NONE, 404, false, 'Status code 404 does not equal 200'],

            // Modifiers::NOT
            [Modifiers::NOT, 200, false, 'Status code 200 equals 200'],
            [Modifiers::NOT, 404, true, ''],
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
            new AssertStatusCode(new FileLocation(FilePath::parse('/tmp/tests'), 10), 200, new Modifiers($modifiers));
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

    /**
     * Test assert with invalid status code.
     *
     * @expectedException \MichaelHall\Webunit\Exceptions\InvalidParameterException
     * @expectedExceptionMessage Status code 0 must be in range 100-599
     */
    public function testAssertWithInvalidStatusCode()
    {
        new AssertStatusCode(new FileLocation(FilePath::parse('/tmp/tests'), 10), 0, new Modifiers());
    }
}
