<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use MichaelHall\Webunit\Exceptions\ParseException;
use MichaelHall\Webunit\Exceptions\ValueParserException;
use MichaelHall\Webunit\Interfaces\ParseContextInterface;
use MichaelHall\Webunit\Interfaces\ValueParserInterface;

/**
 * Class for parsing a text into a value.
 *
 * @since 2.2.0
 */
class ValueParser implements ValueParserInterface
{
    /**
     * Constructs the value parser.
     *
     * @since 2.2.0
     *
     * @param ParseContextInterface $parseContext The parse context.
     */
    public function __construct(ParseContextInterface $parseContext)
    {
        $this->parseContext = $parseContext;
    }

    /**
     * Checks if a variable name is valid.
     *
     * @since 2.2.0
     *
     * @param string $variableName The variable name.
     *
     * @return bool True if variable name is valid, false otherwise.
     */
    public static function isValidVariableName(string $variableName): bool
    {
        return preg_match('/^[a-zA-Z_$][a-zA-Z_$0-9]*$/', $variableName) === 1;
    }

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
    public function parseText(string $text): string
    {
        $text = trim($text);
        $text = $this->fixEscapeCharacters($text);
        $text = $this->replaceVariables($text);

        return $text;
    }

    /**
     * Fixes escape characters in a text.
     *
     * @param string $text The original text.
     *
     * @return string The result.
     */
    private function fixEscapeCharacters(string $text): string
    {
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
                    default                => throw new ValueParserException('Invalid escape sequence "' . self::ESCAPE_CHARACTER . $currentCharacter . '" in "' . $text . '".'),
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

        if ($isAfterEscapeCharacter) {
            throw new ValueParserException('Unterminated escape sequence in "' . $text . '".');
        }

        return $result;
    }

    /**
     * Replaces the variables in a text.
     *
     * @param string $text The original text.
     *
     * @return string The result.
     */
    private function replaceVariables(string $text): string
    {
        $text = preg_replace_callback(
            '/{{(.*?)}}/',
            function (array $matches): string {
                $variableName = trim($matches[1]);

                if ($variableName === '') {
                    throw new ParseException('Missing variable: Missing variable name in "' . $matches[0] . '".');
                } elseif (!self::isValidVariableName($variableName)) {
                    throw new ParseException('Invalid variable: Invalid variable name "' . $variableName . '" in "' . $matches[0] . '".');
                } elseif (!$this->parseContext->hasVariable($variableName)) {
                    throw new ParseException('Invalid variable: No variable with name "' . $variableName . '" is set in "' . $matches[0] . '".');
                }

                return $this->parseContext->getVariable($variableName);
            },
            $text,
        );

        return $text;
    }

    /**
     * @var ParseContextInterface The parse context.
     */
    private ParseContextInterface $parseContext;

    /**
     * The escape character.
     */
    private const ESCAPE_CHARACTER = '\\';
}
