<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Location;

use DataTypes\Interfaces\FilePathInterface;
use MichaelHall\Webunit\Interfaces\LocationInterface;

/**
 * Class representing a location in a file.
 *
 * @since 1.0.0
 */
class FileLocation implements LocationInterface
{
    /**
     * Constructs the file location.
     *
     * @since 1.0.0
     *
     * @param FilePathInterface $file The file.
     * @param int               $line The line.
     */
    public function __construct(FilePathInterface $file, int $line)
    {
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * Returns the location as a string.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->file . ':' . $this->line;
    }

    /**
     * @var FilePathInterface My file.
     */
    private $file;

    /**
     * @var int My line.
     */
    private $line;
}
