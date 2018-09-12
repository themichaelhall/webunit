<?php
/**
 * This file is a part of the webunit package.
 *
 * Read more at https://github.com/themichaelhall/webunit
 */
declare(strict_types=1);

namespace MichaelHall\Webunit\Assertions\Base;

use MichaelHall\Webunit\Exceptions\InvalidRegexpException;
use MichaelHall\Webunit\Exceptions\NotAllowedModifierException;
use MichaelHall\Webunit\Interfaces\LocationInterface;
use MichaelHall\Webunit\Modifiers;

/**
 * Abstract assertion with content base class.
 *
 * @since 1.0.0
 */
abstract class AbstractContentAssert extends AbstractAssert
{
    /**
     * Creates an abstract assert with content.
     *
     * @since 1.0.0
     *
     * @param LocationInterface $location  The location.
     * @param string            $content   The content.
     * @param Modifiers         $modifiers The modifiers.
     *
     * @throws InvalidRegexpException      If modifiers contains regexp and content is not a valid regexp.
     * @throws NotAllowedModifierException If modifiers are not allowed for this assert.
     */
    public function __construct(LocationInterface $location, string $content, Modifiers $modifiers)
    {
        parent::__construct($location, $modifiers);

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        if ($modifiers->isRegexp() && @preg_match('/' . $content . '/', '') === false) {
            throw new InvalidRegexpException($content);
        }

        $this->content = $content;
    }

    /**
     * Returns the content.
     *
     * @since 1.0.0
     *
     * @return string The content.
     */
    protected function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns true if value contains the expected value, taking the current modifiers into account.
     *
     * @since 1.0.0
     *
     * @param string $expected The expected value.
     * @param string $value    The value.
     *
     * @return bool True if value contains the expected value, false otherwise.
     */
    protected function stringContains(string $expected, string $value): bool
    {
        if ($this->getModifiers()->isRegexp()) {
            return preg_match('/' . $expected . '/' . ($this->getModifiers()->isCaseInsensitive() ? 'i' : ''), $value) === 1;
        }

        return $this->getModifiers()->isCaseInsensitive() ?
            mb_stristr($value, $expected) !== false :
            strpos($value, $expected) !== false;
    }

    /**
     * Returns true if strings are equal, taking the current modifiers into account.
     *
     * @since 1.0.0
     *
     * @param string $expected The expected value.
     * @param string $value    The value.
     *
     * @return bool True if strings are equal, false otherwise.
     */
    protected function stringEquals(string $expected, string $value): bool
    {
        if ($this->getModifiers()->isRegexp()) {
            return preg_match('/^' . $expected . '$/' . ($this->getModifiers()->isCaseInsensitive() ? 'i' : ''), $value) === 1;
        }

        return $this->getModifiers()->isCaseInsensitive() ?
            mb_strtolower($expected) === mb_strtolower($value) :
            $expected === $value;
    }

    /**
     * @var string My content.
     */
    private $content;
}
