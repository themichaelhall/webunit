<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use Stringable;

/**
 * Interface for modifiers.
 *
 * @since 1.0.0
 */
interface ModifiersInterface extends Stringable
{
    /**
     * No modifiers.
     *
     * @since 1.0.0
     */
    public const NONE = 0x0000;

    /**
     * Not modifier.
     *
     * @since 1.0.0
     */
    public const NOT = 0x0001;

    /**
     * Case insensitive modifier.
     *
     * @since 1.0.0
     */
    public const CASE_INSENSITIVE = 0x0002;

    /**
     * Regexp modifier.
     *
     * @since 1.0.0
     */
    public const REGEXP = 0x0004;

    /**
     * Combines this with other modifiers.
     *
     * @since 1.0.0
     *
     * @param ModifiersInterface $other The other modifiers.
     *
     * @return ModifiersInterface The combined modifiers.
     */
    public function combinedWith(self $other): self;

    /**
     * Returns true if this contains other modifiers, false otherwise.
     *
     * @since 1.0.0
     *
     * @param ModifiersInterface $other The other modifiers.
     *
     * @return bool True if this contains other modifiers, false otherwise.
     */
    public function contains(self $other): bool;

    /**
     * Returns true if this equals other modifiers, false otherwise.
     *
     * @since 1.0.0
     *
     * @param ModifiersInterface The other modifiers.
     *
     * @return bool True if this equals other modifiers, false otherwise.
     */
    public function equals(self $modifiers): bool;

    /**
     * Returns true if this is a NOT modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True is this is a NOT modifier, false otherwise.
     */
    public function isNot(): bool;

    /**
     * Returns true if this is a CASE_INSENSITIVE modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if this is a CASE_INSENSITIVE modifier, false otherwise.
     */
    public function isCaseInsensitive(): bool;

    /**
     * Returns true if this is a REGEXP modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if this is a REGEXP modifier, false otherwise.
     */
    public function isRegexp(): bool;

    /**
     * Returns the value for this modifier.
     *
     * @since 1.0.0
     *
     * @return int The value.
     */
    public function getValue(): int;
}
