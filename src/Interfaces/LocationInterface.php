<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for a location.
 *
 * @since 1.0.0
 */
interface LocationInterface
{
    /**
     * Returns the location as a string.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function __toString(): string;
}
