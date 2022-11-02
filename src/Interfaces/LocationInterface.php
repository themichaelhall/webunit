<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use DataTypes\System\FilePathInterface;
use Stringable;

/**
 * Interface for a location.
 *
 * @since 1.0.0
 */
interface LocationInterface extends Stringable
{
    /**
     * Returns the file path.
     *
     * @since 2.1.0
     *
     * @return FilePathInterface The file path.
     */
    public function getFilePath(): FilePathInterface;
}
