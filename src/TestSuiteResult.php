<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit;

use MichaelHall\Webunit\Interfaces\TestCaseResultInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteResultInterface;

/**
 * Class representing a test suite result.
 *
 * @since 1.0.0
 */
class TestSuiteResult implements TestSuiteResultInterface
{
    /**
     * Constructs a test suite result.
     *
     * @since 1.0.0
     *
     * @param TestSuiteInterface        $testSuite       The test suite.
     * @param TestCaseResultInterface[] $testCaseResults The test case results.
     */
    public function __construct(TestSuiteInterface $testSuite, array $testCaseResults)
    {
        $this->testSuite = $testSuite;
        $this->testCaseResults = $testCaseResults;

        $this->failedTestCaseResults = [];
        foreach ($testCaseResults as $testCaseResult) {
            if (!$testCaseResult->isSuccess()) {
                $this->failedTestCaseResults[] = $testCaseResult;
            }
        }

        $this->failedTestsCount = count($this->failedTestCaseResults);
        $this->isSuccess = $this->failedTestsCount === 0;
    }

    /**
     * Returns the failed test case results.
     *
     * @since 1.0.0
     *
     * @return TestCaseResultInterface[] The failed test case results.
     */
    public function getFailedTestCaseResults(): array
    {
        return $this->failedTestCaseResults;
    }

    /**
     * Returns the count of failed tests.
     *
     * @since 1.0.0
     *
     * @return int The count of failed tests.
     */
    public function getFailedTestsCount(): int
    {
        return $this->failedTestsCount;
    }

    /**
     * Returns the test case results.
     *
     * @since 1.0.0
     *
     * @return TestCaseResultInterface[] The test case results.
     */
    public function getTestCaseResults(): array
    {
        return $this->testCaseResults;
    }

    /**
     * Returns the test suite.
     *
     * @since 1.0.0
     *
     * @return TestSuiteInterface The test suite.
     */
    public function getTestSuite(): TestSuiteInterface
    {
        return $this->testSuite;
    }

    /**
     * Returns true if tests are successful, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if tests are successful, false otherwise.
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @var TestSuiteInterface My test suite.
     */
    private $testSuite;

    /**
     * @var TestCaseResultInterface[] My test case results.
     */
    private $testCaseResults;

    /**
     * @var TestCaseResultInterface[] My failed test case results.
     */
    private $failedTestCaseResults;

    /**
     * @var bool True if tests are successful, false otherwise.
     */
    private $isSuccess;

    /**
     * @var int The count of failed tests.
     */
    private $failedTestsCount;
}
