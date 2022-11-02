<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Location;

use DataTypes\System\FilePathInterface;
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
     * @param FilePathInterface $filePath The file path.
     * @param int               $line     The line.
     */
    public function __construct(FilePathInterface $filePath, int $line)
    {
        $this->filePath = $filePath;
        $this->line = $line;
    }

    /**
     * Returns the file path.
     *
     * @since 2.1.0
     *
     * @return FilePathInterface The file path.
     */
    public function getFilePath(): FilePathInterface
    {
        return $this->filePath;
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
        return $this->filePath . ':' . $this->line;
    }

    /**
     * @var FilePathInterface The file.
     */
    private FilePathInterface $filePath;

    /**
     * @var int The line.
     */
    private int $line;
}
