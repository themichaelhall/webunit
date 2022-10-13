<?php

/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */

declare(strict_types=1);

namespace MichaelHall\Webunit\Parser;

use MichaelHall\Webunit\Interfaces\ParseContextInterface;

/**
 * Class representing a context used for parsing.
 *
 * @since 1.2.0
 */
class ParseContext implements ParseContextInterface
{
    /**
     * ParseContext constructor.
     *
     * @since 1.2.0
     */
    public function __construct()
    {
        $this->variables = [];
    }

    /**
     * Returns a variable value or null if the variable is not set.
     *
     * @since 1.2.0
     *
     * @param string $name The variable name.
     *
     * @return string|null The variable value or null if the variable is not set.
     */
    public function getVariable(string $name): ?string
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * Returns true if a variable is set or false otherwise.
     *
     * @since 1.2.0
     *
     * @param string $name The variable name.
     *
     * @return bool True if the variable is set or false otherwise.
     */
    public function hasVariable(string $name): bool
    {
        return isset($this->variables[$name]);
    }

    /**
     * Sets a variable.
     *
     * @since 1.2.0
     *
     * @param string $name  The variable name.
     * @param string $value The variable value.
     */
    public function setVariable(string $name, string $value): void
    {
        $this->variables[$name] = $value;
    }

    /**
     * @var array<string, string>
     */
    private $variables;
}
