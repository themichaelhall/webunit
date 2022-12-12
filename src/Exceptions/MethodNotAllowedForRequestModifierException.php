<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Exceptions;

use LogicException;
use MichaelHall\Webunit\Interfaces\RequestModifierInterface;

/**
 * Exception thrown when a request modifier are added to a test case with an incompatible request method.
 *
 * @since 2.1.0
 */
class MethodNotAllowedForRequestModifierException extends LogicException
{
    /**
     * MethodNotAllowedForRequestModifierException constructor.
     *
     * @since 2.1.0
     *
     * @param string                   $method          The request method.
     * @param RequestModifierInterface $requestModifier The request modifier.
     */
    public function __construct(string $method, RequestModifierInterface $requestModifier)
    {
        parent::__construct('Method is not allowed for request modifier.');

        $this->method = $method;
        $this->requestModifier = $requestModifier;
    }

    /**
     * Returns the request method.
     *
     * @since 2.1.0
     *
     * @return string The request method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the request modifier.
     *
     * @since 2.1.0
     *
     * @return RequestModifierInterface The request modifier.
     */
    public function getRequestModifier(): RequestModifierInterface
    {
        return $this->requestModifier;
    }

    /**
     * @var string The request method.
     */
    private string $method;

    /**
     * @var RequestModifierInterface The request modifier.
     */
    private RequestModifierInterface $requestModifier;
}
