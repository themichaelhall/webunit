<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use DataTypes\Exceptions\UrlInvalidArgumentException;
use DataTypes\Interfaces\FilePathInterface;
use DataTypes\Url;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Exceptions\InvalidParameterException;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Interfaces\ParseErrorInterface;
use MichaelHall\Webunit\Interfaces\ParseResultInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteInterface;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\TestCase;
use MichaelHall\Webunit\TestSuite;

/**
 * Class representing a parser.
 *
 * @since 1.0.0
 */
class Parser
{
    /**
     * Parses content into a test suite.
     *
     * @since 1.0.0
     *
     * @param FilePathInterface $filePath The file path.
     * @param string[]          $content  The content.
     *
     * @return ParseResultInterface The parse result.
     */
    public function parse(FilePathInterface $filePath, array $content): ParseResultInterface
    {
        $testSuite = new TestSuite();
        $currentTestCase = null;
        $parseErrors = [];
        $lineNumber = 0;

        foreach ($content as $line) {
            $line = trim($line);
            $lineNumber++;
            $location = new FileLocation($filePath, $lineNumber);

            self::parseLine($location, $line, $testSuite, $currentTestCase, $parseErrors);
        }

        return new ParseResult($testSuite, $parseErrors);
    }

    /**
     * Parses a line.
     *
     * @param LocationInterface      $location        The location.
     * @param string                 $line            The line.
     * @param TestSuiteInterface     $testSuite       The test suite.
     * @param TestCaseInterface|null $currentTestCase The current test case that is parsing or null if no test case is parsing.
     * @param ParseErrorInterface[]  $parseErrors     The current parse errors.
     */
    private static function parseLine(LocationInterface $location, string $line, TestSuiteInterface $testSuite, ?TestCaseInterface &$currentTestCase, array &$parseErrors): void
    {
        if (self::isEmptyOrComment($line)) {
            return;
        }

        $lineParts = preg_split('/\s+/', $line, 2);
        $command = strtolower(trim($lineParts[0]));
        $argument = count($lineParts) > 1 ? trim($lineParts[1]) : null;

        if (self::tryParseTestCase($location, $command, $argument, $parseErrors, $testCase)) {
            if ($testCase !== null) {
                $testSuite->addTestCase($testCase);
                $currentTestCase = $testCase;
            }

            return;
        }

        if (self::tryParseAssert($location, $command, $argument, $parseErrors, $assert)) {
            if ($assert !== null) {
                if ($currentTestCase !== null) {
                    $currentTestCase->addAssert($assert);
                } else {
                    $parseErrors[] = new ParseError($location, 'Undefined test case: Test case is not defined for assert "' . $command . '".');
                }
            }

            return;
        }

        $parseErrors[] = new ParseError($location, 'Syntax error: Invalid command "' . $command . '".');
    }

    /**
     * Checks if a line is empty or a comment.
     *
     * @param string $line The line.
     *
     * @return bool True if line is empty or a comment.
     */
    private static function isEmptyOrComment(string $line): bool
    {
        return $line === '' || $line[0] === '#';
    }

    /**
     * Try parse a test case.
     *
     * @param LocationInterface      $location    The location.
     * @param string                 $command     The command.
     * @param null|string            $argument    The argument or null if no argument.
     * @param ParseErrorInterface[]  $parseErrors The parse errors.
     * @param TestCaseInterface|null $testCase    The parsed test case or null if parsing failed.
     *
     * @return bool True if this was a test case, false otherwise.
     */
    private static function tryParseTestCase(LocationInterface $location, string $command, ?string $argument, array &$parseErrors, ?TestCaseInterface &$testCase): bool
    {
        $testCase = null;

        if ($command !== 'get') {
            return false;
        }

        if ($argument === null) {
            $parseErrors[] = new ParseError($location, 'Missing argument: Missing Url argument for "' . $command . '".');

            return true;
        }

        $url = null;

        try {
            $url = Url::parse($argument);
        } catch (UrlInvalidArgumentException $exception) {
            $parseErrors[] = new ParseError($location, 'Invalid argument: Invalid Url argument "' . $argument . '" for "' . $command . '": ' . $exception->getMessage());

            return true;
        }

        $testCase = new TestCase($location, $url);

        return true;
    }

    /**
     * Try parse an assert.
     *
     * @param LocationInterface    $location    The location.
     * @param string               $command     The command.
     * @param string|null          $argument    The argument or null if no argument.
     * @param array                $parseErrors The parse errors.
     * @param AssertInterface|null $assert      The parses assert or null if parsing failed.
     *
     * @return bool True if this was an assert, false otherwise.
     */
    private static function tryParseAssert(LocationInterface $location, string $command, ?string $argument, array &$parseErrors, ?AssertInterface &$assert): bool
    {
        $assert = null;

        self::extractModifiers($command, $assertString, $modifiersString);

        if (!isset(self::ASSERTS_INFO[$assertString])) {
            return false;
        }

        $assertInfo = self::ASSERTS_INFO[$assertString];
        $className = $assertInfo[0];
        $argumentType = $assertInfo[1];
        $argumentName = $assertInfo[2];

        $argumentResult = self::checkAssertArgument($argument, $argumentType, $argumentValue);
        if ($argumentResult !== self::ARGUMENT_OK) {
            switch ($argumentResult) {
                case self::ARGUMENT_ERROR_EXTRA_ARGUMENT:
                    $parseErrors[] = new ParseError($location, 'Extra argument: "' . $argument . '". No arguments are allowed for assert "' . $command . '".');
                    break;
                case self::ARGUMENT_ERROR_MISSING_ARGUMENT:
                    $parseErrors[] = new ParseError($location, 'Missing argument: Missing ' . $argumentName . ' argument for assert "' . $command . '".');
                    break;
                case self::ARGUMENT_ERROR_INVALID_ARGUMENT_TYPE:
                    $parseErrors[] = new ParseError($location, 'Invalid argument: ' . ucfirst($argumentName) . ' "' . $argument . '" must be of type ' . $argumentType . ' for assert "' . $command . '".');
                    break;
            }

            return true;
        }

        self::tryParseModifiers($location, $assertString, $modifiersString, $parseErrors, $modifiers);
        if ($modifiers === null) {
            return true;
        }

        try {
            $assert = $argumentValue === null ?
                new $className($location, $modifiers) :
                new $className($location, $argumentValue, $modifiers);
        } catch (InvalidParameterException $e) {
            $parseErrors[] = new ParseError($location, 'Invalid argument: ' . $e->getMessage() . ' for assert "' . $command . '".');
        } catch (NotAllowedModifierException $e) {
            $invalidModifiers = [];

            foreach (self::MODIFIERS_INFO as $char => $value) {
                if ($e->getModifiers()->contains(new Modifiers($value))) {
                    $invalidModifiers[] = '"' . $char . '"';
                }
            }

            $isMultiple = count($invalidModifiers) > 1;

            $parseErrors[] = new ParseError($location, 'Invalid modifier' . ($isMultiple ? 's' : '') . ': Modifier' . ($isMultiple ? 's' : '') . ' ' . implode(', ', $invalidModifiers) . ' ' . ($isMultiple ? 'are' : 'is') . ' not allowed for assert "' . $assertString . '".');
        }

        return true;
    }

    /**
     * Checks an assert argument.
     *
     * @param null|string $argument      The argument as a string.
     * @param null|string $argumentType  The argument type or null if no argument.
     * @param null|mixed  $argumentValue The actual argument to use.
     *
     * @return int The ARGUMENT_* result.
     */
    private static function checkAssertArgument(?string $argument, ?string $argumentType, &$argumentValue = null): int
    {
        $argumentValue = $argument;

        if ($argumentType === null) {
            return $argument === null ? self::ARGUMENT_OK : self::ARGUMENT_ERROR_EXTRA_ARGUMENT;
        }

        if ($argument === null) {
            return self::ARGUMENT_ERROR_MISSING_ARGUMENT;
        }

        if ($argumentType === 'integer') {
            $argumentValue = intval($argument);

            if (strval($argumentValue) !== $argument) {
                return self::ARGUMENT_ERROR_INVALID_ARGUMENT_TYPE;
            }
        }

        return self::ARGUMENT_OK;
    }

    /**
     * Extracts modifiers from a command.
     *
     * @param string      $command         The command.
     * @param string|null $assertString    The resulting assert string.
     * @param string|null $modifiersString The resulting modifiers string.
     */
    private static function extractModifiers(string $command, ?string &$assertString, ?string &$modifiersString): void
    {
        $assertString = '';
        $modifiersString = '';

        for ($i = strlen($command) - 1; $i >= 0; $i--) {
            $ch = $command[$i];

            if (!isset(self::MODIFIERS_INFO[$ch])) {
                break;
            }
        }

        $assertString = substr($command, 0, $i + 1);
        $modifiersString = substr($command, $i + 1);
    }

    /**
     * Try parse modifiers.
     *
     * @param LocationInterface       $location        The location.
     * @param string                  $assertString    The assert command string.
     * @param string                  $modifiersString The modifiers string to parse.
     * @param array                   $parseErrors     The parse errors.
     * @param ModifiersInterface|null $modifiers       The modifiers or null if parsing failed.
     */
    private static function tryParseModifiers(LocationInterface $location, string $assertString, string $modifiersString, array &$parseErrors, ?ModifiersInterface &$modifiers): void
    {
        $modifiers = new Modifiers();
        $hasErrors = false;

        for ($i = 0; $i < strlen($modifiersString); $i++) {
            $ch = $modifiersString[$i];
            $newModifier = new Modifiers(self::MODIFIERS_INFO[$ch]);

            if ($modifiers->contains($newModifier)) {
                $parseErrors[] = new ParseError($location, 'Duplicate modifier: Modifier "' . $ch . '" is duplicated for assert "' . $assertString . '".');
                $hasErrors = true;
            } else {
                $modifiers = $modifiers->combinedWith($newModifier);
            }
        }

        if ($hasErrors) {
            $modifiers = null;
        }
    }

    /**
     * Info about the asserts.
     *
     * The format is as follows:
     *
     * name => [0 => className, 1 => argumentType|null, 2 => argumentName|null]
     */
    private const ASSERTS_INFO = [
        'assert-contains'    => [AssertContains::class, 'string', 'content'],
        'assert-empty'       => [AssertEmpty::class, null, null],
        'assert-equals'      => [AssertEquals::class, 'string', 'content'],
        'assert-status-code' => [AssertStatusCode::class, 'integer', 'status code'],
    ];

    /**
     * Info about the modifiers.
     *
     * The format is as follows:
     *
     * modifier-char => modifier-value
     */
    private const MODIFIERS_INFO = [
        '!' => ModifiersInterface::NOT,
        '^' => ModifiersInterface::CASE_INSENSITIVE,
        '~' => ModifiersInterface::REGEXP,
    ];

    /**
     * Argument is ok.
     */
    private const ARGUMENT_OK = 0;

    /**
     * Extra argument error.
     */
    private const ARGUMENT_ERROR_EXTRA_ARGUMENT = 1;

    /**
     * Missing argument error.
     */
    private const ARGUMENT_ERROR_MISSING_ARGUMENT = 2;

    /**
     * Invalid argument type error.
     */
    private const ARGUMENT_ERROR_INVALID_ARGUMENT_TYPE = 3;
}
