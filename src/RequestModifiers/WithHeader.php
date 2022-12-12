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
 * Class representing a request modifier for setting a header.
 *
 * @since 2.1.0
 */
class WithHeader implements RequestModifierInterface
{
    /**
     * WithHeader constructor.
     *
     * @since 2.1.0
     *
     * @param string $headerName  The name of the header.
     * @param string $headerValue The value of the header.
     */
    public function __construct(string $headerName, string $headerValue)
    {
        $this->headerName = $headerName;
        $this->headerValue = $headerValue;
    }

    /**
     * Returns the name of the header.
     *
     * @since 2.1.0
     *
     * @return string The name of the header.
     */
    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    /**
     * Returns the value of the header.
     *
     * @since 2.1.0
     *
     * @return string The value of the header.
     */
    public function getHeaderValue(): string
    {
        return $this->headerValue;
    }

    /**
     * Checks if this request modifier is allowed to use for the specified request method.
     *
     * @since 2.1.0
     *
     * @param string $method The request method.
     *
     * @return bool True if request modifier is allowed, false otherwise.
     */
    public function isAllowedForMethod(string $method): bool
    {
        return true;
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
        $request->addHeader($this->headerName . ': ' . $this->headerValue);
    }

    /**
     * @var string The name of the header.
     */
    private string $headerName;

    /**
     * @var string The value of the header.
     */
    private string $headerValue;
}
