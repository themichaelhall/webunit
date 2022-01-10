<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use MichaelHall\Webunit\Interfaces\ParseErrorInterface;
use MichaelHall\Webunit\Interfaces\ParseResultInterface;
use MichaelHall\Webunit\Interfaces\TestSuiteInterface;

/**
 * Class representing a parse result.
 *
 * @since 1.0.0
 */
class ParseResult implements ParseResultInterface
{
    /**
     * Constructs the parse result.
     *
     * @since 1.0.0
     *
     * @param TestSuiteInterface    $testSuite   The test suite.
     * @param ParseErrorInterface[] $parseErrors The parse errors.
     */
    public function __construct(TestSuiteInterface $testSuite, array $parseErrors)
    {
        $this->testSuite = $testSuite;
        $this->parseErrors = $parseErrors;
        $this->isSuccess = count($parseErrors) === 0;
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
     * Returns the parse errors.
     *
     * @since 1.0.0
     *
     * @return ParseErrorInterface[] The parse errors.
     */
    public function getParseErrors(): array
    {
        return $this->parseErrors;
    }

    /**
     * Returns true if parsing was successful, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if parsing was successful, false otherwise.
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
     * @var ParseErrorInterface[] My parse errors.
     */
    private $parseErrors;

    /**
     * @var bool True if parsing was successful, false otherwise.
     */
    private $isSuccess;
}
