<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Application;

use DataTypes\System\Exceptions\FilePathInvalidArgumentException;
use DataTypes\System\FilePath;
use DataTypes\System\FilePathInterface;
use MichaelHall\HttpClient\HttpClientInterface;
use MichaelHall\Webunit\Interfaces\AssertResultInterface;
use MichaelHall\Webunit\Interfaces\ParseContextInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteResultInterface;
use MichaelHall\Webunit\Parser\ParseContext;
use MichaelHall\Webunit\Parser\Parser;
use MichaelHall\Webunit\Parser\ValueParser;

/**
 * Console application class.
 *
 * @since 1.0.0
 */
class ConsoleApplication
{
    /**
     * All tests passed.
     *
     * @since 1.0.0
     */
    public const RESULT_OK = 0;

    /**
     * No tests found.
     *
     * @since 1.0.0
     */
    public const RESULT_NO_TESTS_FOUND = 1;

    /**
     * Command line parameter error.
     *
     * @since 1.0.0
     */
    public const RESULT_PARAMETER_ERROR = 2;

    /**
     * Error while reading the test file.
     *
     * @since 1.0.0
     */
    public const RESULT_READ_TEST_FILE_ERROR = 3;

    /**
     * Error while parsing the test file.
     *
     * @since 1.0.0
     */
    public const RESULT_PARSE_TEST_FILE_ERROR = 4;

    /**
     * Tests failed.
     *
     * @since 1.0.0
     */
    public const RESULT_TESTS_FAILED = 5;

    /**
     * The current version of webunit.
     *
     * @since 1.2.0
     */
    public const WEBUNIT_VERSION = '2.2.0';

    /**
     * Constructs the console application.
     *
     * @since 1.0.0
     *
     * @param string[]            $commandLineParameters The command line parameters.
     * @param HttpClientInterface $httpClient            The HTTP client.
     */
    public function __construct(array $commandLineParameters, HttpClientInterface $httpClient)
    {
        $this->commandLineParameters = $commandLineParameters;
        $this->httpClient = $httpClient;
    }

    /**
     * Runs the console application.
     *
     * @since 1.0.0
     *
     * @return int The result code.
     */
    public function run(): int
    {
        echo 'Webunit v' . self::WEBUNIT_VERSION . PHP_EOL;

        $parseContext = new ParseContext();
        if (!$this->parseCommandLineParameters($this->commandLineParameters, $parseContext, $testfilePath, $error)) {
            self::fail($error);
            echo 'Usage: webunit [options] testfile' . PHP_EOL;

            return self::RESULT_PARAMETER_ERROR;
        }

        $content = $this->tryReadContent($testfilePath);
        if ($content === null) {
            return self::RESULT_READ_TEST_FILE_ERROR;
        }

        $parser = new Parser();
        $parseResult = $parser->parse($testfilePath, $content, $parseContext);
        if (!$parseResult->isSuccess()) {
            foreach ($parseResult->getParseErrors() as $parseError) {
                echo $parseError . PHP_EOL;
            }

            self::fail('Parsing failed.');

            return self::RESULT_PARSE_TEST_FILE_ERROR;
        }

        $testSuite = $parseResult->getTestSuite();
        if (count($testSuite->getTestCases()) === 0) {
            self::warn('No tests found.');

            return self::RESULT_NO_TESTS_FOUND;
        }

        $currentProgressBarLength = 0;
        $showProgressBarFunction = function (AssertResultInterface $assertResult) use (&$currentProgressBarLength): void {
            if ($currentProgressBarLength === self::PROGRESS_BAR_MAX_LENGTH_PER_LINE) {
                echo PHP_EOL;
                $currentProgressBarLength = 0;
            }

            echo $assertResult->isSuccess() ? '.' : "\033[41m\033[1;37mF\033[0m";
            $currentProgressBarLength++;
        };

        $testResults = $testSuite->run($this->httpClient, $showProgressBarFunction);
        echo PHP_EOL;

        $this->printReport($testResults);

        return $testResults->isSuccess() ? self::RESULT_OK : self::RESULT_TESTS_FAILED;
    }

    /**
     * Parses the command line parameters given to the console application.
     *
     * @param string[]               $parameters   The command line parameters.
     * @param ParseContextInterface  $parseContext The parse context.
     * @param FilePathInterface|null $testfilePath The parsed path to the webunit testfile or null if parsing failed.
     * @param string|null            $error        The error if parsing failed.
     *
     * @return bool True if parsing was successful, false otherwise.
     */
    private function parseCommandLineParameters(array $parameters, ParseContextInterface $parseContext, FilePathInterface &$testfilePath = null, string &$error = null): bool
    {
        array_shift($parameters);
        $testfilePath = null;
        $error = null;

        foreach ($parameters as $parameter) {
            $parameter = trim($parameter);

            if (str_starts_with($parameter, '--')) {
                $optionParts = explode('=', $parameter, 2);
                $optionName = trim($optionParts[0]);
                $optionValue = count($optionParts) > 1 ? trim($optionParts[1]) : null;

                switch ($optionName) {
                    case '--set':
                        if ($optionValue === null) {
                            $error = 'Missing value for option "' . $parameter . '".';

                            return false;
                        }

                        if (!$this->parseSetCommandLineParameter($optionValue, $parseContext, $error)) {
                            $error = 'Invalid value for option "' . $parameter . '": ' . $error;

                            return false;
                        }
                        break;

                    default:
                        $error = 'Invalid option "' . $parameter . '".';

                        return false;
                }

                continue;
            }

            if (!$this->parseTestfilePathCommandLineParameter($parameter, $testfilePath, $error)) {
                return false;
            }
        }

        if ($testfilePath === null) {
            $error = 'Missing testfile parameter.';

            return false;
        }

        return true;
    }

    /**
     * Parses the --set command line parameter value.
     *
     * @param string                $value        The --set command line parameter value.
     * @param ParseContextInterface $parseContext The parse context.
     */
    private function parseSetCommandLineParameter(string $value, ParseContextInterface $parseContext, ?string &$error): bool
    {
        $variableParts = explode('=', $value, 2);

        $variableName = trim($variableParts[0]);
        if ($variableName === '') {
            $error = 'Missing variable name.';

            return false;
        }

        if (!ValueParser::isValidVariableName($variableName)) {
            $error = 'Invalid variable name "' . $variableName . '".';

            return false;
        }

        $variableValue = count($variableParts) > 1 ? trim($variableParts[1]) : null;
        if ($variableValue === null) {
            $error = 'Missing variable value.';

            return false;
        }

        $parseContext->setVariable($variableName, $variableValue);

        return true;
    }

    /**
     * Parses the testfile command line parameter.
     *
     * @param string                 $value        The parameter value.
     * @param FilePathInterface|null $testfilePath The parsed path to the webunit testfile if parsing was successful.
     * @param string|null            $error        The error if parsing failed.
     *
     * @return bool True if parsing was successful, false otherwise.
     */
    private function parseTestfilePathCommandLineParameter(string $value, ?FilePathInterface &$testfilePath, ?string &$error): bool
    {
        if ($testfilePath !== null) {
            $error = 'Extra testfile parameter "' . $value . '".';

            return false;
        }

        try {
            $testfilePath = FilePath::parseAsDirectory(getcwd())->withFilePath(FilePath::parse($value));
        } catch (FilePathInvalidArgumentException $exception) {
            $error = 'Invalid path to testfile "' . $value . '": ' . $exception->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Try to read content from the specified file path.
     *
     * @param FilePathInterface $filePath The file path.
     *
     * @return string[]|null The content or null if failed.
     */
    private function tryReadContent(FilePathInterface $filePath): ?array
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $content = @file($filePath->__toString());
        if ($content === false) {
            self::fail('Could not open file "' . $filePath . '".');

            return null;
        }

        return $content;
    }

    /**
     * Prints a report from the result of the tests.
     *
     * @param TestSuiteResultInterface $testResults The test results.
     */
    private function printReport(TestSuiteResultInterface $testResults): void
    {
        if ($testResults->isSuccess()) {
            $count = $testResults->getSuccessfulTestsCount();
            self::success($count . ' test' . ($count > 1 ? 's' : '') . ' completed successfully.');

            return;
        }

        foreach ($testResults->getFailedTestCaseResults() as $failedTestCaseResult) {
            $failedTestCase = $failedTestCaseResult->getTestCase();
            $failedAssertResult = $failedTestCaseResult->getFailedAssertResult();
            $failedAssert = $failedAssertResult->getAssert();

            echo $failedAssert->getLocation() . ': Test failed: ' . $failedTestCase->getUrl() . ': ' . $failedAssertResult->getError() . '.' . PHP_EOL;
        }

        $count = $testResults->getFailedTestsCount();
        self::fail($count . ' test' . ($count > 1 ? 's' : '') . ' failed.');
    }

    /**
     * Print a success message.
     *
     * @param string $message The message.
     */
    private static function success(string $message): void
    {
        echo "\033[42m\033[30m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Prints a warning message.
     *
     * @param string $message The message.
     */
    private static function warn(string $message): void
    {
        echo "\033[43m\033[30m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Prints an error message.
     *
     * @param string $message The message.
     */
    private static function fail(string $message): void
    {
        echo "\033[41m\033[1;37m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * @var string[] The command line parameters.
     */
    private array $commandLineParameters;

    /**
     * @var HttpClientInterface The HTTP client.
     */
    private HttpClientInterface $httpClient;

    /**
     * @var int The maximum length in characters per line of the "progress bar".
     */
    private const PROGRESS_BAR_MAX_LENGTH_PER_LINE = 70;
}
