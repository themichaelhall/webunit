<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use MichaelHall\Webunit\Interfaces\ValueParserInterface;

/**
 * Class for parsing a text into a value.
 *
 * @since 2.2.0
 */
class ValueParser implements ValueParserInterface
{
    /**
     * Parses a text into a value.
     *
     * @since 2.2.0
     *
     * @param string $text The text.
     *
     * @return string The value.
     */
    public function parseText(string $text): string
    {
        return trim($text);
    }
}
