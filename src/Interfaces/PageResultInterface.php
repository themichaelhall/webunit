<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

/**
 * Interface for a page result.
 *
 * @since 1.0.0
 */
interface PageResultInterface
{
    /**
     * Returns the content.
     *
     * @since 1.0.0
     *
     * @return string The content.
     */
    public function getContent(): string;

    /**
     * Returns the headers.
     *
     * @since 1.1.0
     *
     * @return string[] The headers.
     */
    public function getHeaders(): array;

    /**
     * Returns the status code.
     *
     * @since 1.0.0
     *
     * @return int The status code.
     */
    public function getStatusCode(): int;
}
