<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use MichaelHall\Webunit\Exceptions\ValueParserException;

/**
 * Interface for parsing a text into a value.
 *
 * @since 2.2.0
 */
interface ValueParserInterface
{
    /**
     * Parses a text into a value.
     *
     * @since 2.2.0
     *
     * @param string $text The text.
     *
     * @throws ValueParserException On parse failure.
     *
     * @return string The value.
     */
    public function parseText(string $text): string;
}
