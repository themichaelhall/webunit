<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use MichaelHall\PageFetcher\Interfaces\PageFetcherInterface;

/**
 * Interface for TestSuite class.
 *
 * @since 1.0.0
 */
interface TestSuiteInterface
{
    /**
     * Adds a test case.
     *
     * @since 1.0.0
     *
     * @param TestCaseInterface $testCase The test case.
     */
    public function addTestCase(TestCaseInterface $testCase): void;

    /**
     * Returns the test cases.
     *
     * @since 1.0.0
     *
     * @return TestCaseInterface[] The test cases.
     */
    public function getTestCases(): array;

    /**
     * Runs the test suite.
     *
     * @since 1.0.0
     *
     * @param PageFetcherInterface $pageFetcher The page fetcher.
     * @param callable|null        $callback    An optional callback method to call after each assert. The method takes a AssertResultInterface as a parameter.
     *
     * @return TestSuiteResultInterface The result.
     */
    public function run(PageFetcherInterface $pageFetcher, ?callable $callback = null): TestSuiteResultInterface;
}
