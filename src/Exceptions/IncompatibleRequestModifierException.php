<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Exceptions;

use LogicException;
use MichaelHall\Webunit\Interfaces\RequestModifierInterface;

/**
 * Exception thrown when a request modifiers are added to a test case that already contains an incompatible request modifier.
 *
 * @since 2.1.0
 */
class IncompatibleRequestModifierException extends LogicException
{
    /**
     * IncompatibleRequestModifierException constructor.
     *
     * @since 2.1.0
     *
     * @param RequestModifierInterface $currentRequestModifier The current request modifier.
     * @param RequestModifierInterface $newRequestModifier     The new request modifier.
     */
    public function __construct(RequestModifierInterface $currentRequestModifier, RequestModifierInterface $newRequestModifier)
    {
        parent::__construct('Incompatible request modifier.');

        $this->currentRequestModifier = $currentRequestModifier;
        $this->newRequestModifier = $newRequestModifier;
    }

    /**
     * Returns the current request modifier.
     *
     * @since 2.1.0
     *
     * @return RequestModifierInterface The current request modifier.
     */
    public function getCurrentRequestModifier(): RequestModifierInterface
    {
        return $this->currentRequestModifier;
    }

    /**
     * Returns the new request modifier.
     *
     * @since 2.1.0
     *
     * @return RequestModifierInterface The new request modifier.
     */
    public function getNewRequestModifier(): RequestModifierInterface
    {
        return $this->newRequestModifier;
    }

    /**
     * @var RequestModifierInterface The current request modifier.
     */
    private RequestModifierInterface $currentRequestModifier;

    /**
     * @var RequestModifierInterface The new request modifier.
     */
    private RequestModifierInterface $newRequestModifier;
}
