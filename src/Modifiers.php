<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit;

/**
 * Class handling modifiers.
 *
 * @since 1.0.0
 */
class Modifiers
{
    /**
     * No modifiers.
     *
     * @since 1.0.0
     */
    const NONE = 0x0000;

    /**
     * Not modifier.
     *
     * @since 1.0.0
     */
    const NOT = 0x0001;

    /**
     * Case insensitive modifier.
     *
     * @since 1.0.0
     */
    const CASE_INSENSITIVE = 0x0002;

    /**
     * Regexp modifier.
     *
     * @since 1.0.0
     */
    const REGEXP = 0x0004;

    /**
     * Constructs modifiers.
     *
     * @since 1.0.0
     *
     * @param int $modifiers The modifiers.
     */
    public function __construct(int $modifiers = self::NONE)
    {
        $this->modifiers = $modifiers;
    }

    /**
     * Returns true if this equals other modifiers, false otherwise.
     *
     * @since 1.0.0
     *
     * @param Modifiers $modifiers The other modifiers.
     *
     * @return bool True if this equals other modifiers, false otherwise.
     */
    public function equals(self $modifiers): bool
    {
        return $this->modifiers === $modifiers->modifiers;
    }

    /**
     * Combines this with other modifiers.
     *
     * @since 1.0.0
     *
     * @param Modifiers $other The other modifiers.
     *
     * @return Modifiers The combined modifiers.
     */
    public function combinedWith(self $other): self
    {
        return new self($this->modifiers | $other->modifiers);
    }

    /**
     * Returns true if this is a NOT modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True is this is a NOT modifier, false otherwise.
     */
    public function isNot(): bool
    {
        return ($this->modifiers & self::NOT) !== 0;
    }

    /**
     * Returns true if this is a CASE_INSENSITIVE modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if this is a CASE_INSENSITIVE modifier, false otherwise.
     */
    public function isCaseInsensitive()
    {
        return ($this->modifiers & self::CASE_INSENSITIVE) !== 0;
    }

    /**
     * Returns true if this is a REGEXP modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if this is a REGEXP modifier, false otherwise.
     */
    public function isRegexp()
    {
        return ($this->modifiers & self::REGEXP) !== 0;
    }

    /**
     * Returns the modifiers description as a string.
     *
     * @since 1.0.0
     *
     * @return string The modifiers description as a string.
     */
    public function __toString(): string
    {
        $modifierStrings = [];

        if ($this->isCaseInsensitive()) {
            $modifierStrings[] = 'case insensitive';
        }

        if ($this->isRegexp()) {
            $modifierStrings[] = 'regexp';
        }

        if (count($modifierStrings) === 0) {
            return '';
        }

        return '(' . implode(', ', $modifierStrings) . ')';
    }

    /**
     * @var int My modifiers.
     */
    private $modifiers;
}
