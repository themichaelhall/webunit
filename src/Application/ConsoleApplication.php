<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Application;

use DataTypes\Exceptions\FilePathInvalidArgumentException;
use DataTypes\FilePath;
use MichaelHall\PageFetcher\Interfaces\PageFetcherInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteResultInterface;
use MichaelHall\Webunit\Parser\Parser;

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
    const RESULT_OK = 0;

    /**
     * Command line parameter error.
     *
     * @since 1.0.0
     */
    const RESULT_PARAMETER_ERROR = 1;

    /**
     * Error while reading the test file.
     *
     * @since 1.0.0
     */
    const RESULT_READ_TEST_FILE_ERROR = 2;

    /**
     * Error while parsing the test file.
     *
     * @since 1.0.0
     */
    const RESULT_PARSE_TEST_FILE_ERROR = 3;

    /**
     * Tests failed.
     *
     * @since 1.0.0
     */
    const RESULT_TESTS_FAILED = 4;

    /**
     * Constructs the console application.
     *
     * @since 1.0.0
     *
     * @param int                  $argc        The command line argument count.
     * @param string[]             $argv        The command line arguments.
     * @param PageFetcherInterface $pageFetcher The page fetcher.
     */
    public function __construct(int $argc, array $argv, PageFetcherInterface $pageFetcher)
    {
        $this->argc = $argc;
        $this->argv = $argv;
        $this->pageFetcher = $pageFetcher;
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
        echo 'Webunit [dev] by Michael Hall.' . PHP_EOL;

        if ($this->argc !== 2) {
            self::fail('Usage: webunit testfile');

            return self::RESULT_PARAMETER_ERROR;
        }

        $filePath = null;

        try {
            $filePath = FilePath::parse($this->argv[1]);
        } catch (FilePathInvalidArgumentException $exception) {
            self::fail('Invalid file path "' . $this->argv[1] . '": ' . $exception->getMessage());

            return self::RESULT_READ_TEST_FILE_ERROR;
        }

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $content = @file($filePath->__toString());
        if ($content === false) {
            self::fail('Could not open file "' . $filePath . '".');

            return self::RESULT_READ_TEST_FILE_ERROR;
        }

        $parser = new Parser();
        $parseResult = $parser->parse($filePath, $content);
        if (!$parseResult->isSuccess()) {
            foreach ($parseResult->getParseErrors() as $parseError) {
                echo $parseError . PHP_EOL;
            }

            self::fail('Parsing failed');

            return self::RESULT_PARSE_TEST_FILE_ERROR;
        }

        $testResults = $parseResult->getTestSuite()->run($this->pageFetcher);
        $this->printReport($testResults);

        return $testResults->isSuccess() ? self::RESULT_OK : self::RESULT_TESTS_FAILED;
    }

    /**
     * Prints a report from the result of the tests.
     *
     * @param TestSuiteResultInterface $testResults The test results.
     */
    private function printReport(TestSuiteResultInterface $testResults): void
    {
        if ($testResults->isSuccess()) {
            self::success('Tests completed successfully');

            return;
        }

        foreach ($testResults->getFailedTestCaseResults() as $failedTestCaseResult) {
            $failedTestCase = $failedTestCaseResult->getTestCase();
            $failedAssertResult = $failedTestCaseResult->getFailedAssertResult();
            $failedAssert = $failedAssertResult->getAssert();

            echo $failedAssert->getLocation() . ': Test failed: ' . $failedTestCase->getUrl() . ': ' . $failedAssertResult->getError() . ".\n";
        }

        self::fail('Tests failed');
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
     * Prints an error message.
     *
     * @param string $message The message.
     */
    private static function fail(string $message): void
    {
        echo "\033[41m\033[1;37m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * @var int My command line argument count.
     */
    private $argc;

    /**
     * @var string[] My command line arguments.
     */
    private $argv;

    /**
     * @var PageFetcherInterface My page fetcher.
     */
    private $pageFetcher;
}
