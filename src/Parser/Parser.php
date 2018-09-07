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
            $command = trim($lineParts[0]);
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

        if (strtolower($command) !== 'get') {
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
     * @param LocationInterface $location  The location.
     * @param string            $command   The command.
     * @param null|string       $parameter The parameter or null if no parameter.
     * @param null|string       $error     The error or null if no error.
     *
     * @return AssertInterface|null
     */
    private function tryParseAssert(LocationInterface $location, string $command, ?string $parameter, ?string &$error = null): ?AssertInterface
    {
        $error = null;
        $command = strtolower($command);

        if (!isset(self::ASSERTS_INFO[$command])) {
            return null;
        }

        $assertInfo = self::ASSERTS_INFO[$command];
        $className = $assertInfo[0];
        $argumentType = $assertInfo[1];
        $argumentName = $assertInfo[2];

        // fixme: Check modifiers
        $modifiers = new Modifiers();

        if ($argumentName === null) {
            if ($parameter !== null) {
                $error = 'Extra argument: "' . $parameter . '". No arguments are allowed for assert "' . strtolower($command) . '".';

                return null;
            }

            return new $className($location, $modifiers);
        }

        if ($parameter === null) {
            $error = 'Missing argument: Missing ' . $argumentName . ' argument for assert "' . strtolower($command) . '".';

            return null;
        }

        if ($argumentType === 'integer') {
            $intParameter = intval($parameter);
            if (strval($intParameter) !== $parameter) {
                $error = 'Invalid argument: ' . ucfirst($argumentName) . ' "' . $parameter . '" must be of type ' . $argumentType . ' for assert "' . strtolower($command) . '".';

                return null;
            }

            $parameter = $intParameter;
        }

        try {
            return new $className($location, $parameter, $modifiers);
        } catch (InvalidParameterException $e) {
            $error = 'Invalid argument: ' . $e->getMessage() . ' for assert "' . strtolower($command) . '".';
        }

        return null;
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
}
