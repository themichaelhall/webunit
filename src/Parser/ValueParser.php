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
        $text = trim($text);

        if (!str_contains($text, self::ESCAPE_CHARACTER)) {
            return $text;
        }

        $result = '';
        $isAfterEscapeCharacter = false;
        $textLength = strlen($text);
        for ($i = 0; $i < $textLength; $i++) {
            $currentCharacter = $text[$i];

            if ($isAfterEscapeCharacter) {
                $result .= match ($currentCharacter) {
                    'n'                    => "\n",
                    'r'                    => "\r",
                    's'                    => ' ',
                    't'                    => "\t",
                    self::ESCAPE_CHARACTER => self::ESCAPE_CHARACTER,
                };

                $isAfterEscapeCharacter = false;
                continue;
            }

            if ($currentCharacter === self::ESCAPE_CHARACTER) {
                $isAfterEscapeCharacter = true;
                continue;
            }

            $result .= $currentCharacter;
        }

        return $result;
    }

    /**
     * The escape character.
     */
    private const ESCAPE_CHARACTER = '\\';
}
