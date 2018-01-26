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
     * @param string $content The content.
     */
    public function __construct(string $content)
    {
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
     * @var string My content.
     */
    private $content;
}
