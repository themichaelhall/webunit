<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for AssertResult class.
 *
 * @since 1.0.0
 */
interface AssertResultInterface
{
    /**
     * Returns the assert.
     *
     * @since 1.0.0
     *
     * @return AssertInterface The assert.
     */
    public function getAssert(): AssertInterface;

    /**
     * Returns the error or empty string if no error.
     *
     * @since 1.0.0
     *
     * @return string The error or empty string.
     */
    public function getError(): string;

    /**
     * Returns true if test is successful, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if test is successful, false otherwise.
     */
    public function isSuccess(): bool;
}
