<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for TestCaseResult class.
 *
 * @since 1.0.0
 */
interface TestCaseResultInterface
{
    /**
     * Returns the test case.
     *
     * @since 1.0.0
     *
     * @return TestCaseInterface The test case.
     */
    public function getTestCase(): TestCaseInterface;

    /**
     * Returns true if test is successful, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if test is successful, false otherwise.
     */
    public function isSuccess(): bool;

    /**
     * Returns the failed assert result or null if no assert failed.
     *
     * @since 1.0.0
     *
     * @return AssertResultInterface|null The failed assert result or null if no assert failed.
     */
    public function getFailedAssertResult(): ?AssertResultInterface;
}
