<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Interfaces\ParseErrorInterface;

/**
 * Class representing a parse error.
 *
 * @since 1.0.0
 */
class ParseError implements ParseErrorInterface
{
    /**
     * Constructs the parse error.
     *
     * @since 1.0.0
     *
     * @param LocationInterface $location The location.
     * @param string            $error    The error.
     */
    public function __construct(LocationInterface $location, string $error)
    {
        $this->location = $location;
        $this->error = $error;
    }

    /**
     * Returns the location.
     *
     * @since 1.0.0
     *
     * @return LocationInterface The location.
     */
    public function getLocation(): LocationInterface
    {
        return $this->location;
    }

    /**
     * Returns the error.
     *
     * @since 1.0.0
     *
     * @return string The error.
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @var LocationInterface My location.
     */
    private $location;

    /**
     * @var string My error.
     */
    private $error;
}
