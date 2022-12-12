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
 * Exception thrown when a file was not found.
 *
 * @since 2.1.0
 */
class FileNotFoundException extends LogicException
{
}
