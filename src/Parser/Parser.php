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
use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ParseResultInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
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
            $fileLocation = new FileLocation($filePath, $lineNumber);

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $lineParts = preg_split('/\s+/', $line, 2);
            $command = strtolower(trim($lineParts[0]));
            $parameter = count($lineParts) > 1 ? trim($lineParts[1]) : null;

            $testCase = $this->tryParseTestCase($fileLocation, $command, $parameter, $error);
            if ($testCase !== null) {
                $testSuite->addTestCase($testCase);
                $currentTestCase = $testCase;

                continue;
            }

            if ($error !== null) {
                $parseErrors[] = new ParseError($fileLocation, $error);

                continue;
            }

            $assert = $this->tryParseAssert($fileLocation, $command, $parameter, $error);
            if ($assert !== null) {
                $currentTestCase->addAssert($assert); // fixme: Handle no test case

                continue;
            }

            if ($error !== null) {
                $parseErrors[] = new ParseError($fileLocation, $error);

                continue;
            }

            $parseErrors[] = new ParseError($fileLocation, 'Syntax error: Invalid command "' . $command . '".');
        }

        return new ParseResult($testSuite, $parseErrors);
    }

    /**
     * Try parse a test case.
     *
     * @param LocationInterface $location  The location.
     * @param string            $command   The command.
     * @param null|string       $parameter The parameter or null if no parameter.
     * @param null|string       $error     The error or null if no error.
     *
     * @return TestCaseInterface|null The test case or null if the command was not a start of a test case.
     */
    private function tryParseTestCase(LocationInterface $location, string $command, ?string $parameter, ?string &$error = null): ?TestCaseInterface
    {
        $error = null;

        if ($command !== 'get') {
            return null;
        }

        if ($parameter === null) {
            $error = 'Missing argument: Missing Url argument for "' . $command . '".';

            return null;
        }

        $url = null;

        try {
            $url = Url::parse($parameter);
        } catch (UrlInvalidArgumentException $exception) {
            $error = 'Invalid argument: Invalid Url argument "' . $parameter . '" for "' . $command . '": ' . $exception->getMessage();

            return null;
        }

        return new TestCase($location, $url);
    }

    /**
     * Try parse an assert.
     *
     * @param LocationInterface $location The location.
     * @param string            $command  The command.
     * @param null|string       $argument The argument or null if no argument.
     * @param null|string       $error    The error or null if no error.
     *
     * @return AssertInterface|null
     */
    private function tryParseAssert(LocationInterface $location, string $command, ?string $argument, ?string &$error = null): ?AssertInterface
    {
        $error = null;

        if (!isset(self::ASSERTS_INFO[$command])) {
            return null;
        }

        $assertInfo = self::ASSERTS_INFO[$command];
        $className = $assertInfo[0];
        $argumentType = $assertInfo[1];
        $argumentName = $assertInfo[2];

        $argumentResult = self::checkAssertArgument($argument, $argumentType, $argumentValue);
        if ($argumentResult !== self::ARGUMENT_OK) {
            switch ($argumentResult) {
                case self::ARGUMENT_ERROR_EXTRA_ARGUMENT:
                    $error = 'Extra argument: "' . $argument . '". No arguments are allowed for assert "' . $command . '".';
                    break;
                case self::ARGUMENT_ERROR_MISSING_ARGUMENT:
                    $error = 'Missing argument: Missing ' . $argumentName . ' argument for assert "' . $command . '".';
                    break;
                case self::ARGUMENT_ERROR_INVALID_ARGUMENT_TYPE:
                    $error = 'Invalid argument: ' . ucfirst($argumentName) . ' "' . $argument . '" must be of type ' . $argumentType . ' for assert "' . $command . '".';
                    break;
            }

            return null;
        }

        // fixme: Check modifiers
        $modifiers = new Modifiers();

        try {
            return $argumentValue === null ?
                new $className($location, $modifiers) :
                new $className($location, $argumentValue, $modifiers);
        } catch (InvalidParameterException $e) {
            $error = 'Invalid argument: ' . $e->getMessage() . ' for assert "' . $command . '".';
        }

        return null;
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
     * Info about the asserts.
     *
     * The format is as follows:
     *
     * name => [0 => argumentName|null, 1 => className]
     */
    private const ASSERTS_INFO = [
        'assert-contains'    => [AssertContains::class, 'string', 'content'],
        'assert-empty'       => [AssertEmpty::class, null, null],
        'assert-equals'      => [AssertEquals::class, 'string', 'content'],
        'assert-status-code' => [AssertStatusCode::class, 'integer', 'status code'],
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
