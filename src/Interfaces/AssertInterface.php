<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use MichaelHall\Webunit\Modifiers;

/**
 * Interface for assertions.
 *
 * @since 1.0.0
 */
interface AssertInterface
{
    /**
     * Returns the location.
     *
     * @since 1.0.0
     *
     * @return LocationInterface The location.
     */
    public function getLocation(): LocationInterface;

    /**
     * Returns the modifiers.
     *
     * @since 1.0.0
     *
     * @return Modifiers The modifiers.
     */
    public function getModifiers(): Modifiers;

    /**
     * Test assertion against a page result.
     *
     * @since 1.0.0
     *
     * @param PageResultInterface $pageResult The page result.
     *
     * @return AssertResultInterface The result.
     */
    public function test(PageResultInterface $pageResult): AssertResultInterface;
}
