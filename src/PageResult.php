<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit;

use MichaelHall\Webunit\Interfaces\PageResultInterface;

/**
 * Class representing a page result.
 *
 * @since 1.0.0
 */
class PageResult implements PageResultInterface
{
    /**
     * Constructs the page result.
     *
     * @since 1.0.0
     *
     * @param int      $statusCode The status code.
     * @param string[] $headers    The headers.
     * @param string   $content    The content.
     */
    public function __construct(int $statusCode = 200, array $headers = [], string $content = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->content = $content;
    }

    /**
     * Returns the content.
     *
     * @since 1.0.0
     *
     * @return string The content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns the headers.
     *
     * @since 1.1.0
     *
     * @return string[] The headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the status code.
     *
     * @since 1.0.0
     *
     * @return int The status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @var int My status code.
     */
    private $statusCode;

    /**
     * @var string[] My headers.
     */
    private $headers;

    /**
     * @var string My content.
     */
    private $content;
}
