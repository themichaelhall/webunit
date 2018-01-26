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
     * Test getContent method.
     */
    public function testGetContent()
    {
        $pageResult = new PageResult('Foo');

        self::assertSame('Foo', $pageResult->getContent());
    }
}
