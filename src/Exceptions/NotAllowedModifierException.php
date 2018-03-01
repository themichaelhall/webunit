<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Exceptions;

use MichaelHall\Webunit\Modifiers;

/**
 * Exception thrown when a modifier is not allowed.
 *
 * @since 1.0.0
 */
class NotAllowedModifierException extends \LogicException
{
    /**
     * Constructs the exception.
     *
     * @since 1.0.0
     *
     * @param Modifiers $modifiers The modifiers.
     */
    public function __construct(Modifiers $modifiers)
    {
        parent::__construct('Modifiers are not allowed.');

        $this->modifiers = $modifiers;
    }

    /**
     * Returns the modifiers.
     *
     * @since 1.0.0
     *
     * @return Modifiers The modifiers.
     */
    public function getModifiers(): Modifiers
    {
        return $this->modifiers;
    }

    /**
     * @var Modifiers My modifiers.
     */
    private $modifiers;
}