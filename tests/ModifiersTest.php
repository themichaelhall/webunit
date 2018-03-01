<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use MichaelHall\Webunit\Modifiers;
use PHPUnit\Framework\TestCase;

/**
 * Test Modifiers class.
 */
class ModifiersTest extends TestCase
{
    /**
     * Test isNot method.
     */
    public function testIsNot()
    {
        self::assertFalse((new Modifiers())->isNot());
        self::assertTrue((new Modifiers(Modifiers::NOT))->isNot());
        self::assertFalse((new Modifiers(Modifiers::CASE_INSENSITIVE))->isNot());
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE))->isNot());
    }

    /**
     * Test isCaseInsensitive method.
     */
    public function testIsCaseInsensitive()
    {
        self::assertFalse((new Modifiers())->isCaseInsensitive());
        self::assertFalse((new Modifiers(Modifiers::NOT))->isCaseInsensitive());
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE))->isCaseInsensitive());
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE))->isCaseInsensitive());
    }

    /**
     * Test equals method.
     */
    public function testEquals()
    {
        self::assertTrue((new Modifiers())->equals(new Modifiers()));
        self::assertTrue((new Modifiers(Modifiers::NOT))->equals(new Modifiers(Modifiers::NOT)));
        self::assertFalse((new Modifiers(Modifiers::NOT))->equals(new Modifiers(Modifiers::CASE_INSENSITIVE)));
        self::assertFalse((new Modifiers(Modifiers::CASE_INSENSITIVE))->equals(new Modifiers(Modifiers::NOT)));
        self::assertFalse((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE))->equals(new Modifiers(Modifiers::NOT)));
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE))->equals(new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE)));
    }
}
