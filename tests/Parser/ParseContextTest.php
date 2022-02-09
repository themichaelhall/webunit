<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests\Parser;

use MichaelHall\Webunit\Parser\ParseContext;
use PHPUnit\Framework\TestCase;

/**
 * Test ParseContext class.
 */
class ParseContextTest extends TestCase
{
    /**
     * Test getVariable method.
     */
    public function testGetVariable()
    {
        $parseContext = new ParseContext();

        self::assertNull($parseContext->getVariable('Foo'));
        self::assertNull($parseContext->getVariable('FOO'));
        self::assertNull($parseContext->getVariable('Bar'));
        self::assertNull($parseContext->getVariable('BAR'));
    }

    /**
     * Test setVariable method.
     */
    public function testSetVariable()
    {
        $parseContext = new ParseContext();
        $parseContext->setVariable('FOO', 'baz');
        $parseContext->setVariable('Bar', '12345');

        self::assertNull($parseContext->getVariable('Foo'));
        self::assertSame('baz', $parseContext->getVariable('FOO'));
        self::assertSame('12345', $parseContext->getVariable('Bar'));
        self::assertNull($parseContext->getVariable('BAR'));
    }

    /**
     * Test hasVariable method.
     */
    public function testHasVariable()
    {
        $parseContext = new ParseContext();
        $parseContext->setVariable('FOO', 'baz');
        $parseContext->setVariable('Bar', '12345');

        self::assertFalse($parseContext->hasVariable('Foo'));
        self::assertTrue($parseContext->hasVariable('FOO'));
        self::assertTrue($parseContext->hasVariable('Bar'));
        self::assertFalse($parseContext->hasVariable('BAR'));
    }
}
