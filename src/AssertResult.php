<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit;

use MichaelHall\Webunit\Interfaces\AssertInterface;
use MichaelHall\Webunit\Interfaces\AssertResultInterface;

/**
 * Class representing an assert result.
 *
 * @since 1.0.0
 */
class AssertResult implements AssertResultInterface
{
    /**
     * Constructs a test result.
     *
     * @since 1.0.0
     *
     * @param AssertInterface $assert    The assert.
     * @param bool            $isSuccess True if test is successful, false otherwise.
     * @param string          $error     Error or empty string if no error.
     */
    public function __construct(AssertInterface $assert, bool $isSuccess = true, string $error = '')
    {
        $this->isSuccess = $isSuccess;
        $this->error = $error;
        $this->assert = $assert;
    }

    /**
     * Returns the assert.
     *
     * @since 1.0.0
     *
     * @return AssertInterface The assert.
     */
    public function getAssert(): AssertInterface
    {
        return $this->assert;
    }

    /**
     * Returns the error or empty string if no error.
     *
     * @since 1.0.0
     *
     * @return string The error or empty string.
     */
    public function getError(): string
    {
        return $this->error;
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
     * @var bool True if test is successful, false otherwise.
     */
    private bool $isSuccess;

    /**
     * @var string Error or empty string if no error.
     */
    private string $error;

    /**
     * @var AssertInterface The assert.
     */
    private AssertInterface $assert;
}
