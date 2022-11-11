<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\RequestModifiers;

use MichaelHall\HttpClient\HttpClientRequestInterface;
use MichaelHall\Webunit\Interfaces\RequestModifierInterface;

/**
 * Class representing a request modifier for setting raw body content.
 *
 * @since 2.1.0
 */
class WithRawContent implements RequestModifierInterface
{
    /**
     * WithRawContent constructor.
     *
     * @since 2.1.0
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
     * @since 2.1.0
     *
     * @return string $content The content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Modifies a request.
     *
     * @since 2.1.0
     *
     * @param HttpClientRequestInterface $request
     */
    public function modifyRequest(HttpClientRequestInterface $request): void
    {
        $request->setRawContent($this->content);
    }

    /**
     * @var string The content.
     */
    private string $content;
}
