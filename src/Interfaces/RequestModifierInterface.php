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
     * Modifies a request.
     *
     * @since 2.1.0
     *
     * @param HttpClientRequestInterface $request
     */
    public function modifyRequest(HttpClientRequestInterface $request): void;
}
