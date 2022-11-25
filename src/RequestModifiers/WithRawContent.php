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
     * Checks if this request modifier is compatible with (i.e. can exist on the same test case) as another request modifier.
     *
     * @since 2.1.0
     *
     * @param RequestModifierInterface $requestModifier The other request modifier.
     *
     * @return bool True if request modifiers are compatible, false otherwise.
     */
    public function isCompatibleWith(RequestModifierInterface $requestModifier): bool
    {
        if ($requestModifier instanceof WithPostFile || $requestModifier instanceof WithPostParameter) {
            return false;
        }

        return true;
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
