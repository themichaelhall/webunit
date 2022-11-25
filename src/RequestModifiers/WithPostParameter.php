<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\RequestModifiers;

use MichaelHall\HttpClient\HttpClientRequestInterface;
use MichaelHall\Webunit\Exceptions\EmptyParameterNameException;
use MichaelHall\Webunit\Interfaces\RequestModifierInterface;

/**
 * Class representing a request modifier for POST parameters.
 *
 * @since 2.1.0
 */
class WithPostParameter implements RequestModifierInterface
{
    /**
     * WithPostParameter constructor.
     *
     * @since 2.1.0
     *
     * @param string $parameterName  The name of the POST parameter.
     * @param string $parameterValue The value of the POST parameter.
     *
     * @throws EmptyParameterNameException If the name of the POST parameter is an empty string.
     */
    public function __construct(string $parameterName, string $parameterValue)
    {
        if ($parameterName === '') {
            throw new EmptyParameterNameException('POST parameter name is empty');
        }

        $this->parameterName = $parameterName;
        $this->parameterValue = $parameterValue;
    }

    /**
     * Returns the name of the POST parameter.
     *
     * @since 2.1.0
     *
     * @return string The name of the POST parameter.
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * Returns the value of the POST parameter.
     *
     * @since 2.1.0
     *
     * @return string The value of the POST parameter.
     */
    public function getParameterValue(): string
    {
        return $this->parameterValue;
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
        if ($requestModifier instanceof WithRawContent) {
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
        $request->setPostField($this->parameterName, $this->parameterValue);
    }

    /**
     * @var string The name of the POST parameter.
     */
    private string $parameterName;

    /**
     * @var string The value of the POST parameter.
     */
    private string $parameterValue;
}
