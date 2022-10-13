<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit;

use MichaelHall\Webunit\Interfaces\AssertResultInterface;
use MichaelHall\Webunit\Interfaces\TestCaseInterface;
use MichaelHall\Webunit\Interfaces\TestCaseResultInterface;

/**
 * Class representing a test case result.
 *
 * @since 1.0.0
 */
class TestCaseResult implements TestCaseResultInterface
{
    /**
     * Constructs a test case result.
     *
     * @since 1.0.0
     *
     * @param TestCaseInterface          $testCase           The test case.
     * @param AssertResultInterface|null $failedAssertResult The failed assert result or null if no assert failed.
     */
    public function __construct(TestCaseInterface $testCase, ?AssertResultInterface $failedAssertResult = null)
    {
        $this->testCase = $testCase;
        $this->failedAssertResult = $failedAssertResult;
        $this->isSuccess = $failedAssertResult === null;
    }

    /**
     * Returns the test case.
     *
     * @since 1.0.0
     *
     * @return TestCaseInterface The test case.
     */
    public function getTestCase(): TestCaseInterface
    {
        return $this->testCase;
    }

    /**
     * Returns true if test is successful, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if test is successful, false otherwise.
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * Returns the failed assert result or null if no assert failed.
     *
     * @since 1.0.0
     *
     * @return AssertResultInterface|null The failed assert result or null if no assert failed.
     */
    public function getFailedAssertResult(): ?AssertResultInterface
    {
        return $this->failedAssertResult;
    }

    /**
     * @var TestCaseInterface The test case.
     */
    private TestCaseInterface $testCase;

    /**
     * @var bool True if test is successful, false otherwise.
     */
    private bool $isSuccess;

    /**
     * @var AssertResultInterface|null The failed assert result or null if no assert failed.
     */
    private ?AssertResultInterface $failedAssertResult;
}
