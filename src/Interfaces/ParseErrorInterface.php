<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for ParseError class.
 *
 * @since 1.0.0
 */
interface ParseErrorInterface
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
     * Returns the error.
     *
     * @since 1.0.0
     *
     * @return string The error.
     */
    public function getError(): string;

    /**
     * Returns the error as a string.
     *
     * @since 1.0.0
     *
     * @return string The error as a string.
     */
    public function __toString(): string;
}
