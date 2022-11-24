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
use MichaelHall\Webunit\Exceptions\ParseException;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ModifiersInterface;
use MichaelHall\Webunit\Interfaces\ParseContextInterface;
use MichaelHall\Webunit\Interfaces\ParseResultInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteInterface;
use MichaelHall\Webunit\Location\FileLocation;
use MichaelHall\Webunit\Modifiers;
use MichaelHall\Webunit\RequestModifiers\WithPostFile;
use MichaelHall\Webunit\RequestModifiers\WithPostParameter;
use MichaelHall\Webunit\RequestModifiers\WithRawContent;
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

            try {
                self::tryParseLine($location, $line, $parseContext, $testSuite, $currentTestCase);
            } catch (ParseException $exception) {
                $parseErrors[] = new ParseError($location, $exception->getMessage());
            }
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
     *
     * @throws ParseException If parsing failed.
     */
    private static function tryParseLine(LocationInterface $location, string $line, ParseContextInterface $parseContext, TestSuiteInterface $testSuite, ?TestCaseInterface &$currentTestCase): void
    {
        if (self::isEmptyOrComment($line)) {
            return;
        }

        $lineParts = preg_split('/\s+/', $line, 2);
        $command = strtolower(trim($lineParts[0]));
        $argument = count($lineParts) > 1 ? trim($lineParts[1]) : null;

        if ($argument !== null) {
            self::replaceVariables($argument, $parseContext);
        }

        if (self::tryParseSet($command, $argument, $parseContext)) {
            return;
        }

        if (self::tryParseTestCase($location, $testSuite, $command, $argument, $testCase)) {
            $currentTestCase = $testCase;

            return;
        }

        if (self::tryParseAssert($location, $currentTestCase, $command, $argument)) {
            return;
        }

        if (self::tryParseRequestModifier($location, $currentTestCase, $command, $argument)) {
            return;
        }

        throw new ParseException('Syntax error: Invalid command "' . $command . '".');
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
     * @param string                $content      The text content.
     * @param ParseContextInterface $parseContext The parse context.
     *
     * @throws ParseException If parsing failed.
     */
    private static function replaceVariables(string &$content, ParseContextInterface $parseContext): void
    {
        $content = preg_replace_callback(
            '/{{(.*?)}}/',
            function (array $matches) use ($parseContext): string {
                $variableName = trim($matches[1]);

                if ($variableName === '') {
                    throw new ParseException('Missing variable: Missing variable name in "' . $matches[0] . '".');
                } elseif (!self::isValidVariableName($variableName)) {
                    throw new ParseException('Invalid variable: Invalid variable name "' . $variableName . '" in "' . $matches[0] . '".');
                } elseif (!$parseContext->hasVariable($variableName)) {
                    throw new ParseException('Invalid variable: No variable with name "' . $variableName . '" is set in "' . $matches[0] . '".');
                }

                return $parseContext->getVariable($variableName);
            },
            $content,
        );
    }

    /**
     * Try parse a set command.
     *
     * @param string                $command      The command.
     * @param string|null           $argument     The argument or null if no argument.
     * @param ParseContextInterface $parseContext The parse context.
     *
     * @throws ParseException If parsing failed.
     *
     * @return bool
     */
    private static function tryParseSet(string $command, ?string $argument, ParseContextInterface $parseContext): bool
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
            throw new ParseException('Missing variable: Missing variable name and value for "' . $command . '".');
        }

        $argumentParts = explode('=', $argument, 2);
        $variableName = trim($argumentParts[0]);
        if ($variableName === '') {
            throw new ParseException('Missing variable: Missing variable name for "' . $command . '" in "' . $argument . '".');
        }

        if (!self::isValidVariableName($variableName)) {
            throw new ParseException('Invalid variable: Invalid variable name "' . $variableName . '" for "' . $command . '" in "' . $argument . '".');
        }

        $variableValue = count($argumentParts) > 1 ? trim($argumentParts[1]) : null;
        if ($variableValue === null) {
            throw new ParseException('Missing variable: Missing variable value for "' . $command . '" in "' . $argument . '".');
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
     * @param LocationInterface      $location  The location.
     * @param TestSuiteInterface     $testSuite The test suite.
     * @param string                 $command   The command.
     * @param null|string            $argument  The argument or null if no argument.
     * @param TestCaseInterface|null $testCase  The parsed test case or null if parsing failed.
     *
     * @throws ParseException If parsing failed.
     *
     * @return bool True if this was a test case, false otherwise.
     */
    private static function tryParseTestCase(LocationInterface $location, TestSuiteInterface $testSuite, string $command, ?string $argument, ?TestCaseInterface &$testCase): bool
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
            throw new ParseException('Missing argument: Missing Url argument for "' . $command . '".');
        }

        try {
            $url = Url::parse($argument);
        } catch (UrlInvalidArgumentException $exception) {
            throw new ParseException('Invalid argument: Invalid Url argument "' . $argument . '" for "' . $command . '": ' . $exception->getMessage());
        }

        $testCase = new TestCase($location, $method, $url);
        $testSuite->addTestCase($testCase);

        return true;
    }

    /**
     * Try parse an assert.
     *
     * @param LocationInterface      $location The location.
     * @param TestCaseInterface|null $testCase The test case.
     * @param string                 $command  The command.
     * @param string|null            $argument The argument or null if no argument.
     *
     * @throws ParseException If parsing failed.
     *
     * @return bool True if this was an assert, false otherwise.
     */
    private static function tryParseAssert(LocationInterface $location, ?TestCaseInterface $testCase, string $command, ?string $argument): bool
    {
        self::extractModifiers($command, $assertString, $modifiersString);

        try {
            switch ($assertString) {
                case 'assert-contains':
                    self::tryParseModifiers($assertString, $modifiersString, $modifiers);
                    self::checkAssertArgument($command, $argument, self::ARGUMENT_TYPE_STRING, 'content', $argumentValue);

                    $assert = new AssertContains($location, $argumentValue, $modifiers);

                    break;

                case 'assert-empty':
                    self::tryParseModifiers($assertString, $modifiersString, $modifiers);
                    self::checkAssertArgument($command, $argument, self::ARGUMENT_TYPE_NONE);

                    $assert = new AssertEmpty($location, $modifiers);

                    break;

                case 'assert-equals':
                    self::tryParseModifiers($assertString, $modifiersString, $modifiers);
                    self::checkAssertArgument($command, $argument, self::ARGUMENT_TYPE_STRING, 'content', $argumentValue);

                    $assert = new AssertEquals($location, $argumentValue, $modifiers);

                    break;

                case 'assert-header':
                    self::tryParseModifiers($assertString, $modifiersString, $modifiers);
                    self::checkAssertArgument($command, $argument, self::ARGUMENT_TYPE_STRING, 'header', $argumentValue);

                    $assert = new AssertHeader($location, $argumentValue, $modifiers);

                    break;

                case 'assert-status-code':
                    self::tryParseModifiers($assertString, $modifiersString, $modifiers);
                    self::checkAssertArgument($command, $argument, self::ARGUMENT_TYPE_INTEGER, 'status code', $argumentValue);

                    $assert = new AssertStatusCode($location, $argumentValue, $modifiers);

                    break;

                default:
                    return false;
            }
        } catch (InvalidParameterException $e) {
            throw new ParseException('Invalid argument: ' . $e->getMessage() . ' for assert "' . $command . '".');
        } catch (NotAllowedModifierException $e) {
            $invalidModifiers = [];

            foreach (self::MODIFIERS_INFO as $char => $value) {
                if ($e->getModifiers()->contains(new Modifiers($value))) {
                    $invalidModifiers[] = '"' . $char . '"';
                }
            }

            $isMultiple = count($invalidModifiers) > 1;

            throw new ParseException('Invalid modifier' . ($isMultiple ? 's' : '') . ': Modifier' . ($isMultiple ? 's' : '') . ' ' . implode(', ', $invalidModifiers) . ' ' . ($isMultiple ? 'are' : 'is') . ' not allowed for assert "' . $assertString . '".');
        }

        if ($testCase === null) {
            throw new ParseException('Undefined test case: Test case is not defined for assert "' . $command . '".');
        }

        $testCase->addAssert($assert);

        return true;
    }

    /**
     * Try parse a request modifier.
     *
     * @param LocationInterface      $location The location.
     * @param TestCaseInterface|null $testCase The test case.
     * @param string                 $command  The command.
     * @param string|null            $argument The argument or null if no argument.
     *
     * @throws ParseException If parsing failed.
     *
     * @return bool True if this was a request modifier, false otherwise.
     */
    private static function tryParseRequestModifier(LocationInterface $location, ?TestCaseInterface $testCase, string $command, ?string $argument): bool
    {
        switch ($command) {
            case 'with-post-parameter':
                self::checkMethodIsNotGetForRequestModifier($testCase, $command);
                self::tryParsePostRequestModifierParameter($command, $argument, $parameterName, $parameterValue);

                $requestModifier = new WithPostParameter($parameterName, $parameterValue);

                break;

            case 'with-post-file':
                self::checkMethodIsNotGetForRequestModifier($testCase, $command);
                self::tryParsePostRequestModifierParameter($command, $argument, $parameterName, $parameterValue);

                try {
                    $filePath = $location->getFilePath()->withFilePath(FilePath::parse($parameterValue));
                    $requestModifier = new WithPostFile($parameterName, $filePath);
                } catch (FilePathInvalidArgumentException) {
                    throw new ParseException('Invalid argument: File path "' . $parameterValue . '" is not valid for request modifier "' . $command . '".');
                } catch (FileNotFoundException) {
                    throw new ParseException('Invalid argument: File "' . $parameterValue . '" was not found for request modifier "' . $command . '".');
                }

                break;

            case 'with-raw-content':
                self::checkMethodIsNotGetForRequestModifier($testCase, $command);

                if ($argument === null) {
                    throw new ParseException('Missing argument: Missing content for request modifier "' . $command . '".');
                }

                $requestModifier = new WithRawContent($argument);

                break;

            default:
                return false;
        }

        if ($testCase === null) {
            throw new ParseException('Undefined test case: Test case is not defined for request-modifier "' . $command . '".');
        }

        $testCase->addRequestModifier($requestModifier);

        return true;
    }

    /**
     * Checks an assert argument.
     *
     * @param string          $command       The command.
     * @param null|string     $argument      The argument as a string.
     * @param int             $argumentType  The argument type as one of the ARGUMENT_TYPE_* constants.
     * @param null|string     $argumentName  The argument name or null if no argument.
     * @param string|int|null $argumentValue The actual argument to use.
     *
     * @throws ParseException If check failed.
     */
    private static function checkAssertArgument(string $command, ?string $argument, int $argumentType, ?string $argumentName = null, string|int|null &$argumentValue = null): void
    {
        $argumentValue = $argument;

        if ($argumentType === self::ARGUMENT_TYPE_NONE) {
            if ($argument !== null) {
                throw new ParseException('Extra argument: "' . $argument . '". No arguments are allowed for assert "' . $command . '".');
            }

            return;
        }

        if ($argument === null) {
            throw new ParseException('Missing argument: Missing ' . $argumentName . ' argument for assert "' . $command . '".');
        }

        if ($argumentType === self::ARGUMENT_TYPE_INTEGER) {
            $argumentValue = intval($argument);

            if (strval($argumentValue) !== $argument) {
                throw new ParseException('Invalid argument: ' . ucfirst($argumentName) . ' "' . $argument . '" must be of type integer for assert "' . $command . '".');
            }
        }
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
     * @param string                  $assertString    The assert command string.
     * @param string                  $modifiersString The modifiers string to parse.
     * @param ModifiersInterface|null $modifiers       The modifiers.
     *
     * @throws ParseException If parsing failed.
     */
    private static function tryParseModifiers(string $assertString, string $modifiersString, ?ModifiersInterface &$modifiers): void
    {
        $modifiers = new Modifiers();

        for ($i = 0; $i < strlen($modifiersString); $i++) {
            $ch = $modifiersString[$i];
            $newModifier = new Modifiers(self::MODIFIERS_INFO[$ch]);

            if ($modifiers->contains($newModifier)) {
                throw new ParseException('Duplicate modifier: Modifier "' . $ch . '" is duplicated for assert "' . $assertString . '".');
            }

            $modifiers = $modifiers->combinedWith($newModifier);
        }
    }

    /**
     * Checks if the method is not GET for a request modifier.
     *
     * @param TestCaseInterface|null $testCase
     * @param string                 $command
     *
     * @throws ParseException If parsing failed.
     */
    private static function checkMethodIsNotGetForRequestModifier(?TestCaseInterface $testCase, string $command): void
    {
        if ($testCase !== null && $testCase->getMethod() === TestCaseInterface::METHOD_GET) {
            throw new ParseException('Invalid request modifier: Request modifier "' . $command . '" is not allowed for request method "' . $testCase->getMethod() . '".');
        }
    }

    /**
     * Try parse request modifier parameter for a POST request.
     *
     * @param string      $command        The command.
     * @param string|null $argument       The argument or null if no argument.
     * @param string|null $parameterName  The parsed parameter name.
     * @param string|null $parameterValue The parsed parameter value.
     *
     * @throws ParseException If parsing failed.
     */
    private static function tryParsePostRequestModifierParameter(string $command, ?string $argument, ?string &$parameterName = null, ?string &$parameterValue = null): void
    {
        if ($argument === null) {
            throw new ParseException('Missing argument: Missing parameter name and value for request modifier "' . $command . '".');
        }

        $argumentParts = explode('=', $argument, 2);

        $parameterName = trim($argumentParts[0]);
        if ($parameterName === '') {
            throw new ParseException('Missing argument: Missing parameter name for request modifier "' . $command . '".');
        }

        if (count($argumentParts) < 2) {
            throw new ParseException('Missing argument: Missing parameter value for request modifier "' . $command . '".');
        }

        $parameterValue = trim($argumentParts[1]);
    }

    /**
     * @var int No argument type.
     */
    private const ARGUMENT_TYPE_NONE = 0;

    /**
     * @var int String argument type.
     */
    private const ARGUMENT_TYPE_STRING = 1;

    /**
     * @var int Integer argument type.
     */
    private const ARGUMENT_TYPE_INTEGER = 2;

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
}
