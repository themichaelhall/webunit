<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Exceptions;

use LogicException;

/**
 * Exception thrown when a regexp is invalid.
 *
 * @since 1.0.0
 */
class InvalidRegexpException extends LogicException
{
    /**
     * Constructs the exception.
     *
     * @since 1.0.0
     *
     * @param string $regexp The regexp.
     */
    public function __construct(string $regexp)
    {
        parent::__construct('Regexp "' . $regexp . '" is invalid.');
    }
}
