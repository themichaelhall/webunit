<?php

declare(strict_types=1);

namespace MichaelHall\Webunit\Tests;

use MichaelHall\Webunit\Interfaces\ModifiersInterface;
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
        self::assertTrue((new Modifiers(ModifiersInterface::NOT))->isNot());
        self::assertFalse((new Modifiers(ModifiersInterface::CASE_INSENSITIVE))->isNot());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->isNot());
        self::assertFalse((new Modifiers(ModifiersInterface::REGEXP))->isNot());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::REGEXP))->isNot());
        self::assertFalse((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->isNot());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->isNot());
    }

    /**
     * Test isCaseInsensitive method.
     */
    public function testIsCaseInsensitive()
    {
        self::assertFalse((new Modifiers())->isCaseInsensitive());
        self::assertFalse((new Modifiers(ModifiersInterface::NOT))->isCaseInsensitive());
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE))->isCaseInsensitive());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->isCaseInsensitive());
        self::assertFalse((new Modifiers(ModifiersInterface::REGEXP))->isCaseInsensitive());
        self::assertFalse((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::REGEXP))->isCaseInsensitive());
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->isCaseInsensitive());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->isCaseInsensitive());
    }

    /**
     * Test isRegexp method.
     */
    public function testIsRegexp()
    {
        self::assertFalse((new Modifiers())->isRegexp());
        self::assertFalse((new Modifiers(ModifiersInterface::NOT))->isRegexp());
        self::assertFalse((new Modifiers(ModifiersInterface::CASE_INSENSITIVE))->isRegexp());
        self::assertFalse((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->isRegexp());
        self::assertTrue((new Modifiers(ModifiersInterface::REGEXP))->isRegexp());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::REGEXP))->isRegexp());
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->isRegexp());
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->isRegexp());
    }

    /**
     * Test __toString method.
     */
    public function testToString()
    {
        self::assertSame('', (new Modifiers())->__toString());
        self::assertSame('', (new Modifiers(ModifiersInterface::NOT))->__toString());
        self::assertSame('(case insensitive)', (new Modifiers(ModifiersInterface::CASE_INSENSITIVE))->__toString());
        self::assertSame('(case insensitive)', (new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->__toString());
        self::assertSame('(regexp)', (new Modifiers(ModifiersInterface::REGEXP))->__toString());
        self::assertSame('(regexp)', (new Modifiers(ModifiersInterface::NOT | ModifiersInterface::REGEXP))->__toString());
        self::assertSame('(case insensitive, regexp)', (new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->__toString());
        self::assertSame('(case insensitive, regexp)', (new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->__toString());
    }

    /**
     * Test equals method.
     */
    public function testEquals()
    {
        self::assertTrue((new Modifiers())->equals(new Modifiers()));
        self::assertTrue((new Modifiers(ModifiersInterface::NOT))->equals(new Modifiers(ModifiersInterface::NOT)));
        self::assertFalse((new Modifiers(ModifiersInterface::NOT))->equals(new Modifiers(ModifiersInterface::CASE_INSENSITIVE)));
        self::assertFalse((new Modifiers(ModifiersInterface::CASE_INSENSITIVE))->equals(new Modifiers(ModifiersInterface::NOT)));
        self::assertFalse((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->equals(new Modifiers(ModifiersInterface::NOT)));
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->equals(new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE)));
    }

    /**
     * Test combinedWith method.
     */
    public function testCombinedWith()
    {
        $modifiers1 = new Modifiers();
        $modifiers2 = new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP);
        $modifiers3 = new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT);

        self::assertTrue((new Modifiers())->equals($modifiers1->combinedWith($modifiers1)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->equals($modifiers1->combinedWith($modifiers2)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT))->equals($modifiers1->combinedWith($modifiers3)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->equals($modifiers2->combinedWith($modifiers1)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::REGEXP))->equals($modifiers2->combinedWith($modifiers2)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT | ModifiersInterface::REGEXP))->equals($modifiers2->combinedWith($modifiers3)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT))->equals($modifiers3->combinedWith($modifiers1)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT | ModifiersInterface::REGEXP))->equals($modifiers3->combinedWith($modifiers2)));
        self::assertTrue((new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT))->equals($modifiers3->combinedWith($modifiers3)));
    }

    /**
     * Test getValue method.
     */
    public function testGetValue()
    {
        self::assertSame(ModifiersInterface::NONE, (new Modifiers())->getValue());
        self::assertSame(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT, (new Modifiers(ModifiersInterface::CASE_INSENSITIVE | ModifiersInterface::NOT))->getValue());
    }

    /**
     * Test contains method.
     */
    public function testContains()
    {
        self::assertTrue((new Modifiers())->contains(new Modifiers()));
        self::assertFalse((new Modifiers())->contains(new Modifiers(ModifiersInterface::NOT)));
        self::assertTrue((new Modifiers(ModifiersInterface::NOT))->contains(new Modifiers()));
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->contains(new Modifiers()));
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->contains(new Modifiers(ModifiersInterface::NOT)));
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->contains(new Modifiers(ModifiersInterface::CASE_INSENSITIVE)));
        self::assertTrue((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->contains(new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE)));
        self::assertFalse((new Modifiers(ModifiersInterface::NOT | ModifiersInterface::CASE_INSENSITIVE))->contains(new Modifiers(ModifiersInterface::NOT | ModifiersInterface::REGEXP)));
    }
}
