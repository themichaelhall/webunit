<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use DataTypes\Net\Exceptions\UrlInvalidArgumentException;
use DataTypes\Net\Url;
use DataTypes\System\Exceptions\FilePathInvalidArgumentException;
use DataTypes\System\FilePath;
use DataTypes\System\FilePathInterface;
use MichaelHall\Webunit\Assertions\AssertContains;
use MichaelHall\Webunit\Assertions\AssertEmpty;
use MichaelHall\Webunit\Assertions\AssertEquals;
use MichaelHall\Webunit\Assertions\AssertHeader;
use MichaelHall\Webunit\Assertions\AssertStatusCode;
use MichaelHall\Webunit\Exceptions\FileNotFoundException;
use MichaelHall\Webunit\Exceptions\InvalidParameterException;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Interfaces\ParseContextInterface;
use MichaelHall\Webunit\Interfaces\ParseErrorInterface;
use MichaelHall\Webunit\Interfaces\ParseResultInterface;
use MichaelHall\Webunit\Interfaces\RequestModifierInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteInterface;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\RequestModifiers\WithPostFile;
use MichaelHall\Webunit\RequestModifiers\WithPostParameter;
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
     * @param FilePathInterface     $filePath     The file path.
     * @param string[]              $content      The content.
     * @param ParseContextInterface $parseContext The parse context.
     *
     * @return ParseResultInterface The parse result.
     */
    public function parse(FilePathInterface $filePath, array $content, ParseContextInterface $parseContext): ParseResultInterface
    {
        $testSuite = new TestSuite();
        $currentTestCase = null;
        $parseErrors = [];
        $lineNumber = 0;

        foreach ($content as $line) {
            $line = trim($line);
            $lineNumber++;
            $location = new FileLocation($filePath, $lineNumber);

            self::parseLine($location, $line, $parseContext, $testSuite, $currentTestCase, $parseErrors);
        }

        return new ParseResult($testSuite, $parseErrors);
    }

    /**
     * Checks if a variable name is valid.
     *
     * @since 1.3.0
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
     * Parses a line.
     *
     * @param LocationInterface      $location        The location.
     * @param string                 $line            The line.
     * @param ParseContextInterface  $parseContext    The parse context.
     * @param TestSuiteInterface     $testSuite       The test suite.
     * @param TestCaseInterface|null $currentTestCase The current test case that is parsing or null if no test case is parsing.
     * @param ParseErrorInterface[]  $parseErrors     The current parse errors.
     */
    private static function parseLine(LocationInterface $location, string $line, ParseContextInterface $parseContext, TestSuiteInterface $testSuite, ?TestCaseInterface &$currentTestCase, array &$parseErrors): void
    {
        if (self::isEmptyOrComment($line)) {
            return;
        }

        $lineParts = preg_split('/\s+/', $line, 2);
        $command = strtolower(trim($lineParts[0]));
        $argument = count($lineParts) > 1 ? trim($lineParts[1]) : null;

        if ($argument !== null) {
            $argument = self::replaceVariables($location, $argument, $parseContext, $parseErrors);
            if ($argument === null) {
                return;
            }
        }

        if (self::tryParseSet($location, $command, $argument, $parseContext, $parseErrors)) {
            return;
        }

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

        if (self::tryParseRequestModifier($location, $currentTestCase, $command, $argument, $parseErrors, $requestModifier)) {
            if ($requestModifier !== null) {
                if ($currentTestCase !== null) {
                    $currentTestCase->addRequestModifier($requestModifier);
                } else {
                    $parseErrors[] = new ParseError($location, 'Undefined test case: Test case is not defined for request-modifier "' . $command . '".');
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
     * Replaces variables like {{ Foo }} in a text with the actual variable content.
     *
     * @param LocationInterface     $location     The location.
     * @param string                $content      The text content.
     * @param ParseContextInterface $parseContext The parse context.
     * @param ParseErrorInterface[] $parseErrors  The parse errors.
     *
     * @return string|null The text content with variables replaces with the actual variable content or null if replace failed.
     */
    private static function replaceVariables(LocationInterface $location, string $content, ParseContextInterface $parseContext, array &$parseErrors): ?string
    {
        $hasErrors = false;
        $result = preg_replace_callback(
            '/{{(.*?)}}/',
            function (array $matches) use ($location, $parseContext, &$parseErrors, &$hasErrors): string {
                $variableName = trim($matches[1]);
                $error = null;

                if ($variableName === '') {
                    $error = 'Missing variable: Missing variable name in "' . $matches[0] . '".';
                } elseif (!self::isValidVariableName($variableName)) {
                    $error = 'Invalid variable: Invalid variable name "' . $variableName . '" in "' . $matches[0] . '".';
                } elseif (!$parseContext->hasVariable($variableName)) {
                    $error = 'Invalid variable: No variable with name "' . $variableName . '" is set in "' . $matches[0] . '".';
                }

                if ($error !== null) {
                    $hasErrors = true;
                    $parseErrors[] = new ParseError($location, $error);

                    return '';
                }

                return $parseContext->getVariable($variableName);
            },
            $content,
        );

        return !$hasErrors ? $result : null;
    }

    /**
     * Try parse a set command.
     *
     * @param LocationInterface     $location     The location.
     * @param string                $command      The command.
     * @param string|null           $argument     The argument or null if no argument.
     * @param ParseContextInterface $parseContext The parse context.
     * @param ParseErrorInterface[] $parseErrors  The parse errors.
     *
     * @return bool
     */
    private static function tryParseSet(LocationInterface $location, string $command, ?string $argument, ParseContextInterface $parseContext, array &$parseErrors): bool
    {
        switch ($command) {
            case 'set':
                $isDefaultSet = false;
                break;
            case 'set-default':
                $isDefaultSet = true;
                break;
            default:
                return false;
        }

        if ($argument === null) {
            $parseErrors[] = new ParseError($location, 'Missing variable: Missing variable name and value for "' . $command . '".');

            return true;
        }

        $argumentParts = explode('=', $argument, 2);
        $variableName = trim($argumentParts[0]);
        if ($variableName === '') {
            $parseErrors[] = new ParseError($location, 'Missing variable: Missing variable name for "' . $command . '" in "' . $argument . '".');

            return true;
        }

        if (!self::isValidVariableName($variableName)) {
            $parseErrors[] = new ParseError($location, 'Invalid variable: Invalid variable name "' . $variableName . '" for "' . $command . '" in "' . $argument . '".');

            return true;
        }

        $variableValue = count($argumentParts) > 1 ? trim($argumentParts[1]) : null;
        if ($variableValue === null) {
            $parseErrors[] = new ParseError($location, 'Missing variable: Missing variable value for "' . $command . '" in "' . $argument . '".');

            return true;
        }

        if ($isDefaultSet && $parseContext->hasVariable($variableName)) {
            return true;
        }

        $parseContext->setVariable($variableName, $variableValue);

        return true;
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

        $method = match ($command) {
            'delete' => TestCaseInterface::METHOD_DELETE,
            'get'    => TestCaseInterface::METHOD_GET,
            'patch'  => TestCaseInterface::METHOD_PATCH,
            'post'   => TestCaseInterface::METHOD_POST,
            'put'    => TestCaseInterface::METHOD_PUT,
            default  => null,
        };

        if ($method === null) {
            return false;
        }

        if ($argument === null) {
            $parseErrors[] = new ParseError($location, 'Missing argument: Missing Url argument for "' . $command . '".');

            return true;
        }

        try {
            $url = Url::parse($argument);
        } catch (UrlInvalidArgumentException $exception) {
            $parseErrors[] = new ParseError($location, 'Invalid argument: Invalid Url argument "' . $argument . '" for "' . $command . '": ' . $exception->getMessage());

            return true;
        }

        $testCase = new TestCase($location, $method, $url);

        return true;
    }

    /**
     * Try parse an assert.
     *
     * @param LocationInterface    $location    The location.
     * @param string               $command     The command.
     * @param string|null          $argument    The argument or null if no argument.
     * @param ParseError[]         $parseErrors The parse errors.
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
            $parseErrors[] = match ($argumentResult) {
                self::ARGUMENT_ERROR_EXTRA_ARGUMENT        => new ParseError($location, 'Extra argument: "' . $argument . '". No arguments are allowed for assert "' . $command . '".'),
                self::ARGUMENT_ERROR_MISSING_ARGUMENT      => new ParseError($location, 'Missing argument: Missing ' . $argumentName . ' argument for assert "' . $command . '".'),
                self::ARGUMENT_ERROR_INVALID_ARGUMENT_TYPE => new ParseError($location, 'Invalid argument: ' . ucfirst($argumentName) . ' "' . $argument . '" must be of type ' . $argumentType . ' for assert "' . $command . '".'),
            };

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
     * Try parse a request modifier.
     *
     * @param LocationInterface             $location        The location.
     * @param TestCaseInterface|null        $testCase        The test case.
     * @param string                        $command         The command.
     * @param string|null                   $argument        The argument or null if no argument.
     * @param ParseError[]                  $parseErrors     The parse errors.
     * @param RequestModifierInterface|null $requestModifier The parsed request modifier or null if parsing failed.
     *
     * @return bool True if this was a request modifier, false otherwise.
     */
    private static function tryParseRequestModifier(LocationInterface $location, ?TestCaseInterface $testCase, string $command, ?string $argument, array &$parseErrors, ?RequestModifierInterface &$requestModifier): bool
    {
        $requestModifier = null;

        switch ($command) {
            case 'with-post-parameter':
                if (!self::tryParsePostRequestModifierParameter($location, $testCase, $command, $argument, $parseErrors, $parameterName, $parameterValue)) {
                    return true;
                }

                $requestModifier = new WithPostParameter($parameterName, $parameterValue);

                break;

            case 'with-post-file':
                if (!self::tryParsePostRequestModifierParameter($location, $testCase, $command, $argument, $parseErrors, $parameterName, $parameterValue)) {
                    return true;
                }

                try {
                    $filePath = $location->getFilePath()->withFilePath(FilePath::parse($parameterValue));
                    $requestModifier = new WithPostFile($parameterName, $filePath);
                } catch (FilePathInvalidArgumentException) {
                    $parseErrors[] = new ParseError($location, 'Invalid argument: File path "' . $parameterValue . '" is not valid for request modifier "' . $command . '".');

                    return true;
                } catch (FileNotFoundException) {
                    $parseErrors[] = new ParseError($location, 'Invalid argument: File "' . $parameterValue . '" was not found for request modifier "' . $command . '".');

                    return true;
                }

                break;
        }

        return $requestModifier !== null;
    }

    /**
     * Checks an assert argument.
     *
     * @param null|string     $argument      The argument as a string.
     * @param null|string     $argumentType  The argument type or null if no argument.
     * @param string|int|null $argumentValue The actual argument to use.
     *
     * @return int The ARGUMENT_* result.
     */
    private static function checkAssertArgument(?string $argument, ?string $argumentType, string|int|null &$argumentValue = null): int
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
     * @param ParseError[]            $parseErrors     The parse errors.
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
     * Try parse request modifier parameter for a POST request.
     *
     * @param LocationInterface      $location       The location.
     * @param TestCaseInterface|null $testCase       The test case.
     * @param string                 $command        The command.
     * @param string|null            $argument       The argument or null if no argument.
     * @param ParseError[]           $parseErrors    The parse errors.
     * @param string|null            $parameterName  The parsed parameter name.
     * @param string|null            $parameterValue The parsed parameter value.
     *
     * @return bool True if parsing was successful, false otherwise.
     */
    private static function tryParsePostRequestModifierParameter(LocationInterface $location, ?TestCaseInterface $testCase, string $command, ?string $argument, array &$parseErrors, ?string &$parameterName = null, ?string &$parameterValue = null): bool
    {
        if ($testCase !== null && $testCase->getMethod() === TestCaseInterface::METHOD_GET) {
            $parseErrors[] = new ParseError($location, 'Invalid request modifier: Request modifier "' . $command . '" is not allowed for request method "' . $testCase->getMethod() . '".');

            return false;
        }

        if ($argument === null) {
            $parseErrors[] = new ParseError($location, 'Missing argument: Missing parameter name and value for request modifier "' . $command . '".');

            return false;
        }

        $argumentParts = explode('=', $argument, 2);

        $parameterName = trim($argumentParts[0]);
        if ($parameterName === '') {
            $parseErrors[] = new ParseError($location, 'Missing argument: Missing parameter name for request modifier "' . $command . '".');

            return false;
        }

        if (count($argumentParts) < 2) {
            $parseErrors[] = new ParseError($location, 'Missing argument: Missing parameter value for request modifier "' . $command . '".');

            return false;
        }

        $parameterValue = trim($argumentParts[1]);

        return true;
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
        'assert-header'      => [AssertHeader::class, 'string', 'header'],
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
