<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for TestSuiteResult class.
 *
 * @since 1.0.0
 */
interface TestSuiteResultInterface
{
    /**
     * Returns the failed test case results.
     *
     * @since 1.0.0
     *
     * @return TestCaseResultInterface[] The failed test case results.
     */
    public function getFailedTestCaseResults(): array;

    /**
     * Returns the count of failed tests.
     *
     * @since 1.0.0
     *
     * @return int The count of failed tests.
     */
    public function getFailedTestsCount(): int;

    /**
     * Returns the count of successful tests.
     *
     * @since 1.0.0
     *
     * @return int The count of successful tests.
     */
    public function getSuccessfulTestsCount(): int;

    /**
     * Returns the test case results.
     *
     * @since 1.0.0
     *
     * @return TestCaseResultInterface[] The test case results.
     */
    public function getTestCaseResults(): array;

    /**
     * Returns the test suite.
     *
     * @since 1.0.0
     *
     * @return TestSuiteInterface The test suite.
     */
    public function getTestSuite(): TestSuiteInterface;

    /**
     * Returns true if tests are successful, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if tests are successful, false otherwise.
     */
    public function isSuccess(): bool;
}
