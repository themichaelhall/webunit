<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Assertions;

use DataTypes\FilePath;
use MichaelHall\Webunit\Assertions\DefaultAssert;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test DefaultAssert class.
 */
class DefaultAssertTest extends TestCase
{
    /**
     * Test assertion.
     *
     * @dataProvider assertionDataProvider
     *
     * @param int    $statusCode      The status code.
     * @param bool   $expectedSuccess True the expected result is success, false otherwise.
     * @param string $expectedError   The expected error.
     */
    public function testAssertion(int $statusCode, bool $expectedSuccess, string $expectedError)
    {
        $location = new FileLocation(FilePath::parse('/tmp/tests'), 10);
        $assert = new DefaultAssert($location);
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
            [100, false, 'Status code 100 was returned'],
            [199, false, 'Status code 199 was returned'],
            [200, true, ''],
            [299, true, ''],
            [300, false, 'Status code 300 was returned'],
            [400, false, 'Status code 400 was returned'],
            [500, false, 'Status code 500 was returned'],
        ];
    }
}
