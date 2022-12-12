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
 * Exception thrown when a parameter name is empty.
 *
 * @since 2.1.0
 */
class EmptyParameterNameException extends LogicException
{
}
