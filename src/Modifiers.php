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
     * @var int My modifiers.
     */
    private $modifiers;
}
