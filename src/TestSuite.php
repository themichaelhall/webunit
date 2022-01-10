<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit;

use MichaelHall\HttpClient\HttpClientInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteResultInterface;

/**
 * Class representing a test suite.
 *
 * @since 1.0.0
 */
class TestSuite implements TestSuiteInterface
{
    /**
     * Constructs the test suite.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->testCases = [];
    }

    /**
     * Adds a test case.
     *
     * @since 1.0.0
     *
     * @param TestCaseInterface $testCase The test case.
     */
    public function addTestCase(TestCaseInterface $testCase): void
    {
        $this->testCases[] = $testCase;
    }

    /**
     * Returns the test cases.
     *
     * @since 1.0.0
     *
     * @return TestCaseInterface[] The test cases.
     */
    public function getTestCases(): array
    {
        return $this->testCases;
    }

    /**
     * Runs the test suite.
     *
     * @since 1.0.0
     *
     * @param HttpClientInterface $httpClient The HTTP client.
     * @param callable|null       $callback   An optional callback method to call after each assert. The method takes a AssertResultInterface as a parameter.
     *
     * @return TestSuiteResultInterface The result.
     */
    public function run(HttpClientInterface $httpClient, ?callable $callback = null): TestSuiteResultInterface
    {
        $testCaseResults = [];
        foreach ($this->testCases as $testCase) {
            $testCaseResults[] = $testCase->run($httpClient, $callback);
        }

        return new TestSuiteResult($this, $testCaseResults);
    }

    /**
     * @var TestCaseInterface[] My test cases.
     */
    private $testCases;
}
