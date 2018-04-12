<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use MichaelHall\Webunit\PageResult;
use PHPUnit\Framework\TestCase;

/**
 * Test PageResult class.
 */
class PageResultTest extends TestCase
{
    /**
     * Test default constructor.
     */
    public function testDefaultConstructor()
    {
        $pageResult = new PageResult();

        self::assertSame(200, $pageResult->getStatusCode());
        self::assertSame('', $pageResult->getContent());
    }

    /**
     * Test constructor.
     */
    public function testConstructor()
    {
        $pageResult = new PageResult(404, 'Foo');

        self::assertSame(404, $pageResult->getStatusCode());
        self::assertSame('Foo', $pageResult->getContent());
    }
}
