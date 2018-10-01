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
        self::assertFalse((new Modifiers(Modifiers::REGEXP))->isNot());
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::REGEXP))->isNot());
        self::assertFalse((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->isNot());
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->isNot());
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
        self::assertFalse((new Modifiers(Modifiers::REGEXP))->isCaseInsensitive());
        self::assertFalse((new Modifiers(Modifiers::NOT | Modifiers::REGEXP))->isCaseInsensitive());
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->isCaseInsensitive());
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->isCaseInsensitive());
    }

    /**
     * Test isRegexp method.
     */
    public function testIsRegexp()
    {
        self::assertFalse((new Modifiers())->isRegexp());
        self::assertFalse((new Modifiers(Modifiers::NOT))->isRegexp());
        self::assertFalse((new Modifiers(Modifiers::CASE_INSENSITIVE))->isRegexp());
        self::assertFalse((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE))->isRegexp());
        self::assertTrue((new Modifiers(Modifiers::REGEXP))->isRegexp());
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::REGEXP))->isRegexp());
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->isRegexp());
        self::assertTrue((new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->isRegexp());
    }

    /**
     * Test __toString method.
     */
    public function testToString()
    {
        self::assertSame('', (new Modifiers())->__toString());
        self::assertSame('', (new Modifiers(Modifiers::NOT))->__toString());
        self::assertSame('(case insensitive)', (new Modifiers(Modifiers::CASE_INSENSITIVE))->__toString());
        self::assertSame('(case insensitive)', (new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE))->__toString());
        self::assertSame('(regexp)', (new Modifiers(Modifiers::REGEXP))->__toString());
        self::assertSame('(regexp)', (new Modifiers(Modifiers::NOT | Modifiers::REGEXP))->__toString());
        self::assertSame('(case insensitive, regexp)', (new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->__toString());
        self::assertSame('(case insensitive, regexp)', (new Modifiers(Modifiers::NOT | Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->__toString());
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

    /**
     * Test combinedWith method.
     */
    public function testCombinedWith()
    {
        $modifiers1 = new Modifiers();
        $modifiers2 = new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP);
        $modifiers3 = new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::NOT);

        self::assertTrue((new Modifiers())->equals($modifiers1->combinedWith($modifiers1)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->equals($modifiers1->combinedWith($modifiers2)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::NOT))->equals($modifiers1->combinedWith($modifiers3)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->equals($modifiers2->combinedWith($modifiers1)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::REGEXP))->equals($modifiers2->combinedWith($modifiers2)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::NOT | Modifiers::REGEXP))->equals($modifiers2->combinedWith($modifiers3)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::NOT))->equals($modifiers3->combinedWith($modifiers1)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::NOT | Modifiers::REGEXP))->equals($modifiers3->combinedWith($modifiers2)));
        self::assertTrue((new Modifiers(Modifiers::CASE_INSENSITIVE | Modifiers::NOT))->equals($modifiers3->combinedWith($modifiers3)));
    }
}
