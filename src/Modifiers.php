<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit;

use MichaelHall\Webunit\Interfaces\ModifiersInterface;

/**
 * Class handling modifiers.
 *
 * @since 1.0.0
 */
class Modifiers implements ModifiersInterface
{
    /**
     * Constructs modifiers.
     *
     * @since 1.0.0
     *
     * @param int $value The value.
     */
    public function __construct(int $value = ModifiersInterface::NONE)
    {
        $this->value = $value;
    }

    /**
     * Combines this with other modifiers.
     *
     * @since 1.0.0
     *
     * @param ModifiersInterface $other The other modifiers.
     *
     * @return Modifiers The combined modifiers.
     */
    public function combinedWith(ModifiersInterface $other): ModifiersInterface
    {
        return new self($this->value | $other->getValue());
    }

    /**
     * Returns true if this contains other modifiers, false otherwise.
     *
     * @since 1.0.0
     *
     * @param ModifiersInterface $other The other modifiers.
     *
     * @return bool True if this contains other modifiers, false otherwise.
     */
    public function contains(ModifiersInterface $other): bool
    {
        return ($this->value & $other->getValue()) === $other->getValue();
    }

    /**
     * Returns true if this equals other modifiers, false otherwise.
     *
     * @since 1.0.0
     *
     * @param ModifiersInterface $modifiers The other modifiers.
     *
     * @return bool True if this equals other modifiers, false otherwise.
     */
    public function equals(ModifiersInterface $modifiers): bool
    {
        return $this->value === $modifiers->getValue();
    }

    /**
     * Returns the value for this modifier.
     *
     * @since 1.0.0
     *
     * @return int The value.
     */
    public function getValue(): int
    {
        return $this->value;
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
        return ($this->value & ModifiersInterface::NOT) !== 0;
    }

    /**
     * Returns true if this is a CASE_INSENSITIVE modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if this is a CASE_INSENSITIVE modifier, false otherwise.
     */
    public function isCaseInsensitive(): bool
    {
        return ($this->value & ModifiersInterface::CASE_INSENSITIVE) !== 0;
    }

    /**
     * Returns true if this is a REGEXP modifier, false otherwise.
     *
     * @since 1.0.0
     *
     * @return bool True if this is a REGEXP modifier, false otherwise.
     */
    public function isRegexp(): bool
    {
        return ($this->value & ModifiersInterface::REGEXP) !== 0;
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
     * @var int The value.
     */
    private int $value;
}
