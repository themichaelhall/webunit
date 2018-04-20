<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Location;

use DataTypes\FilePath;
use MichaelHall\Webunit\Location\FileLocation;
use PHPUnit\Framework\TestCase;

/**
 * Test FileLocation class.
 */
class FileLocationTest extends TestCase
{
    /**
     * Test constructing a file location.
     */
    public function testConstruct()
    {
        $fileLocation = new FileLocation(FilePath::parse('/foo/bar'), 123);

        self::assertSame('/foo/bar:123', $fileLocation->__toString());
    }
}
