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
use MichaelHall\Webunit\Parser\Parser;

/**
 * Console application class.
 *
 * @since 1.0.0
 */
class ConsoleApplication
{
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
            return self::fail('Usage: webunit testfile', 1);
        }

        $filePath = null;
        try {
            $filePath = FilePath::parse($this->argv[1]);
        } catch (FilePathInvalidArgumentException $exception) {
            return self::fail('Invalid file path "' . $this->argv[1] . '": ' . $exception->getMessage(), 2);
        }

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $content = @file($filePath->__toString());
        if ($content === false) {
            return self::fail('Could not open file "' . $filePath . '".', 2);
        }

        $parser = new Parser();
        $parseResult = $parser->parse($filePath, $content);
        if (!$parseResult->isSuccess()) {
            foreach ($parseResult->getParseErrors() as $parseError) {
                echo $parseError . PHP_EOL;
            }

            return self::fail('Parsing failed', 3);
        }

        $testResults = $parseResult->getTestSuite()->run($this->pageFetcher);
        if (!$testResults->isSuccess()) {
            foreach ($testResults->getFailedTestCaseResults() as $failedTestCaseResult) {
                $failedTestCase = $failedTestCaseResult->getTestCase();
                $failedAssertResult = $failedTestCaseResult->getFailedAssertResult();
                $failedAssert = $failedAssertResult->getAssert();

                echo $failedAssert->getLocation() . ': Test failed: ' . $failedTestCase->getUrl() . ': ' . $failedAssertResult->getError() . ".\n";
            }

            return self::fail('Tests failed', 4);
        }

        self::success('Tests completed successfully');

        return 0;
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
     * @param string $message    The message.
     * @param int    $resultCode The result code.
     *
     * @return int The result code.
     */
    private static function fail(string $message, int $resultCode): int
    {
        echo "\033[41m\033[1;37m" . $message . "\033[0m" . PHP_EOL;

        return $resultCode;
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
