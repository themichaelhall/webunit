<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for ParseResult class.
 *
 * @since 1.0.0
 */
interface ParseResultInterface
{
    /**
     * Returns the test suite.
     *
     * @since 1.0.0
     *
     * @return TestSuiteInterface The test suite.
     */
    public function getTestSuite(): TestSuiteInterface;

    /**
     * Returns the parse errors.
     *
     * @since 1.0.0
     *
     * @return ParseErrorInterface[] The parse errors.
     */
    public function getParseErrors(): array;

    /**
     * Returns true if parsing was successful, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if parsing was successful, false otherwise.
     */
    public function isSuccess(): bool;
}
