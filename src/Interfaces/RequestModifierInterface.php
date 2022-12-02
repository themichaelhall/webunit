<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Interfaces;

use MichaelHall\HttpClient\HttpClientRequestInterface;

/**
 * Interface for a request modifier.
 *
 * @since 2.1.0
 */
interface RequestModifierInterface
{
    /**
     * Checks if this request modifier is allowed to use for the specified request method.
     *
     * @since 2.1.0
     *
     * @param string $method The request method.
     *
     * @return bool True if request modifier is allowed, false otherwise.
     */
    public function isAllowedForMethod(string $method): bool;

    /**
     * Checks if this request modifier is compatible with (i.e. can exist on the same test case) as another request modifier.
     *
     * @since 2.1.0
     *
     * @param RequestModifierInterface $requestModifier The other request modifier.
     *
     * @return bool True if request modifiers are compatible, false otherwise.
     */
    public function isCompatibleWith(RequestModifierInterface $requestModifier): bool;

    /**
     * Modifies a request.
     *
     * @since 2.1.0
     *
     * @param HttpClientRequestInterface $request
     */
    public function modifyRequest(HttpClientRequestInterface $request): void;
}
